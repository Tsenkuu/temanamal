/*
|--------------------------------------------------------------------------
| File: apiwa/index.js (Optimized)
|--------------------------------------------------------------------------
|
| Server Node.js untuk WhatsApp Gateway menggunakan @whiskeysockets/baileys.
| Dioptimalkan untuk stabilitas, manajemen sesi yang lebih aman, dan struktur kode yang bersih.
|
*/

require("dotenv").config();
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
} = require("@whiskeysockets/baileys");
const pino = require("pino");
const express = require("express");
const QRCode = require("qrcode"); // Import di level atas untuk performa
const qrcodeTerminal = require("qrcode-terminal");
const axios = require("axios");
const cheerio = require("cheerio");
const fs = require("fs");
const cors = require("cors");
const path = require("path");

const app = express();
app.use(cors());
app.use(express.json());
app.use(express.urlencoded({ extended: true }));

// Konfigurasi
const PORT = process.env.PORT || 3002;
const API_TOKEN = process.env.API_TOKEN;
const AUTH_DIR = path.join(__dirname, "auth_info_baileys"); // Gunakan path absolute agar aman

// State Variables
let sock;
let lastQRCodeBase64 = null;
let lastQRCodeTimestamp = null;
let connectionStatus = {
  connected: false,
  qrAvailable: false,
  message: "Initializing...",
};

/**
 * ==========================================
 * FUNGSI UTAMA KONEKSI WHATSAPP
 * ==========================================
 */
async function connectToWhatsApp() {
  const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

  sock = makeWASocket({
    logger: pino({ level: "silent" }),
    printQRInTerminal: true,
    auth: state,
    connectTimeoutMs: 60000, // Timeout koneksi diperpanjang
    defaultQueryTimeoutMs: 60000,
  });

  sock.ev.on("connection.update", async (update) => {
    const { connection, lastDisconnect, qr } = update;

    if (qr) {
      console.log("Scan QR Code dibawah ini:");
      // Generate QR untuk Terminal
      qrcodeTerminal.generate(qr, { small: true });
      
      // Generate QR Base64 untuk API
      try {
        lastQRCodeBase64 = await QRCode.toDataURL(qr);
        lastQRCodeTimestamp = new Date();
        connectionStatus = {
          connected: false,
          qrAvailable: true,
          message: "Waiting for QR scan",
        };
      } catch (err) {
        console.error("Gagal generate QR Base64:", err);
      }
    }

    if (connection === "close") {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      connectionStatus = {
        connected: false,
        qrAvailable: false,
        message: `Connection closed. ${shouldReconnect ? "Reconnecting..." : "Logged out."}`,
      };

      console.log(
        `Koneksi terputus (Status: ${statusCode}). Reconnect: ${shouldReconnect}`
      );

      if (shouldReconnect) {
        connectToWhatsApp();
      } else {
        // Jika logged out (logout manual dari HP), hapus sesi
        console.log("Sesi berakhir (Logged Out). Menghapus kredensial...");
        deleteAuthFolder();
      }
    } else if (connection === "open") {
      console.log("✅ Berhasil terhubung ke WhatsApp!");
      lastQRCodeBase64 = null;
      lastQRCodeTimestamp = null;
      connectionStatus = {
        connected: true,
        qrAvailable: false,
        message: "Connected",
      };
    }
  });

  sock.ev.on("creds.update", saveCreds);
}

/**
 * ==========================================
 * HELPER FUNCTIONS
 * ==========================================
 */

// Fungsi aman menghapus folder sesi
function deleteAuthFolder() {
  try {
    if (fs.existsSync(AUTH_DIR)) {
      fs.rmSync(AUTH_DIR, { recursive: true, force: true });
      console.log(`Folder sesi dihapus: ${AUTH_DIR}`);
    }
  } catch (e) {
    console.error("Gagal menghapus folder auth:", e.message);
  }
}

// Format nomor HP ke standar 628xxx@s.whatsapp.net
const formatPhoneNumber = (number) => {
  let formatted = number.toString().replace(/\D/g, ""); // Pastikan string dan hapus non-digit
  if (formatted.startsWith("0")) {
    formatted = "62" + formatted.slice(1);
  } else if (formatted.startsWith("8")) {
    formatted = "62" + formatted;
  }
  return `${formatted}@s.whatsapp.net`;
};

// Middleware Validasi Token
const validateToken = (req, res, next) => {
  const token = req.body.token || req.query.token || req.headers["x-api-key"];
  if (!API_TOKEN) {
    console.warn("API_TOKEN belum diset di .env");
    return next(); // Bolehkan lewat jika dev lupa set token (opsional, sebaiknya diblokir di production)
  }
  if (token !== API_TOKEN) {
    return res.status(401).json({ success: false, message: "Unauthorized: Invalid Token" });
  }
  next();
};

/**
 * ==========================================
 * API ENDPOINTS
 * ==========================================
 */

// 1. Kirim Pesan
app.post("/kirim-pesan", validateToken, async (req, res) => {
  const { nomor, pesan, gambar_url } = req.body;

  if (!nomor || !pesan) {
    return res.status(400).json({ success: false, message: "Nomor dan pesan wajib diisi" });
  }

  if (!sock || !connectionStatus.connected) {
    return res.status(503).json({ success: false, message: "WhatsApp belum terhubung" });
  }

  try {
    const formattedNomor = formatPhoneNumber(nomor);
    
    // Cek apakah nomor terdaftar di WA
    // Gunakan try-catch karena onWhatsApp bisa timeout
    let onWaResult;
    try {
        [onWaResult] = await sock.onWhatsApp(formattedNomor);
    } catch (e) {
        console.error("Gagal cek onWhatsApp:", e.message);
        // Lanjut mencoba kirim meskipun cek gagal (optimistic send)
        onWaResult = { exists: true, jid: formattedNomor }; 
    }

    if (onWaResult && onWaResult.exists) {
      const jid = onWaResult.jid || formattedNomor;
      
      if (gambar_url) {
        await sock.sendMessage(jid, {
          image: { url: gambar_url },
          caption: pesan,
        });
      } else {
        await sock.sendMessage(jid, { text: pesan });
      }

      return res.status(200).json({ success: true, message: "Pesan berhasil dikirim" });
    } else {
      return res.status(404).json({ success: false, message: "Nomor tidak terdaftar di WhatsApp" });
    }
  } catch (error) {
    console.error("Error kirim pesan:", error);
    res.status(500).json({
      success: false,
      message: "Gagal mengirim pesan (Server Error)",
      error: error.message,
    });
  }
});

// 2. Reset Sesi
app.post("/reset", validateToken, async (req, res) => {
  console.log("Permintaan reset sesi diterima...");
  
  // 1. Coba logout socket jika aktif
  try {
    if (sock) {
        await sock.logout();
        sock = null;
    }
  } catch (e) {
    console.warn("Logout error (diabaikan):", e.message);
  }

  // 2. Reset variabel state
  lastQRCodeBase64 = null;
  lastQRCodeTimestamp = null;
  connectionStatus = { connected: false, qrAvailable: false, message: "Resetting..." };

  // 3. Hapus folder auth fisik
  deleteAuthFolder();

  // 4. Mulai ulang
  setTimeout(() => {
      connectToWhatsApp();
  }, 1000);

  res.json({
    success: true,
    message: "Sesi berhasil direset. Silakan tunggu QR Code baru.",
  });
});

// 3. Scraping Kalkulator Zakat
app.get("/kalkulator-details", async (req, res) => {
  try {
    const { data } = await axios.get("https://sobatberbagi.com/kalkulator-zakat/emas", {
        timeout: 10000 // Timeout 10 detik agar tidak hang
    });
    const $ = cheerio.load(data);

    // Helper parsing yang lebih robust
    const parseValue = (text) => {
        if (!text) return 0;
        // Hapus Rp, titik, spasi, dan karakter non-angka
        const cleanText = text.replace(/[^0-9]/g, "");
        return parseInt(cleanText, 10) || 0;
    };

    const getPrice = (label) => {
        const el = $(`#note-zakat ul li:contains('${label}') strong`);
        return parseValue(el.text());
    };

    const hargaEmasPerGram = getPrice("Harga emas per gram");
    const nisabBulanan = getPrice("Nishab 85 gram per bulan");
    const nisabTahunan = getPrice("Nishab 85 gram per tahun");
    const hargaGabahPerKg = getPrice("Harga gabah per kg");
    const nisabPertanian = getPrice("Nishab Zakat Pertanian");

    if (hargaEmasPerGram === 0) {
        throw new Error("Gagal parsing data (selector mungkin berubah)");
    }

    res.json({
      success: true,
      source: "sobatberbagi.com",
      data: {
        harga_emas_per_gram: hargaEmasPerGram,
        harga_gabah_per_kg: hargaGabahPerKg,
        nisab_maal_tahunan: nisabTahunan,
        nisab_maal_bulanan: nisabBulanan,
        nisab_pertanian: nisabPertanian,
      },
    });

  } catch (error) {
    console.error("Gagal mengambil detail kalkulator:", error.message);
    
    // Fallback Data
    const hargaEmasDefault = 1400000; // Update harga estimasi
    res.status(500).json({
      success: false,
      message: "Gagal scraping data realtime, menggunakan data fallback.",
      data: {
        harga_emas_per_gram: hargaEmasDefault,
        harga_gabah_per_kg: 7000,
        nisab_maal_tahunan: 85 * hargaEmasDefault,
        nisab_maal_bulanan: (85 * hargaEmasDefault) / 12,
        nisab_pertanian: 653 * 7000,
      },
    });
  }
});

// 4. Cek Status & QR
app.get("/status", validateToken, (req, res) => {
  res.json({
    success: true,
    data: connectionStatus,
    qr: lastQRCodeBase64 ? {
        base64: lastQRCodeBase64,
        timestamp: lastQRCodeTimestamp
    } : null
  });
});

// Jalankan Server
app.listen(PORT, () => {
  console.log(`\n🚀 Server API WhatsApp berjalan di port ${PORT}`);
  console.log(`🔗 Endpoint: http://localhost:${PORT}`);
  
  // Mulai koneksi WA
  connectToWhatsApp();
});