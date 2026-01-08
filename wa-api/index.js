/*
|--------------------------------------------------------------------------
| File: apiwa/index.js (Fixed & Optimized)
|--------------------------------------------------------------------------
|
| Fitur Baru:
| 1. Pairing Code Support (Lebih stabil dari QR).
| 2. Browser Identity (Agar QR muncul lebih cepat).
| 3. Auto Reconnect yang lebih pintar.
|
*/

require("dotenv").config();
const {
  default: makeWASocket,
  useMultiFileAuthState,
  DisconnectReason,
  fetchLatestBaileysVersion,
  makeCacheableSignalKeyStore,
  Browsers, // Penting agar terdeteksi sebagai browser valid
  delay
} = require("@whiskeysockets/baileys");
const pino = require("pino");
const express = require("express");
const QRCode = require("qrcode");
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

// --- KONFIGURASI ---
const PORT = process.env.PORT || 3001;
const API_TOKEN = process.env.API_TOKEN || "12345"; // Default token jika env kosong
const AUTH_DIR = path.join(__dirname, "auth_info_baileys");
const USE_PAIRING_CODE = process.env.USE_PAIRING_CODE === 'true'; // Set true di .env untuk pakai kode, false untuk QR
const PHONE_NUMBER = process.env.PHONE_NUMBER || ""; // Wajib diisi jika USE_PAIRING_CODE = true (format: 628xx)

// --- STATE VARIABLES ---
let sock;
let lastQRCodeBase64 = null;
let lastQRCodeTimestamp = null;
let lastPairingCode = null; // Menyimpan pairing code terakhir
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
  // 1. Ambil versi WA terbaru agar tidak outdated
  const { version, isLatest } = await fetchLatestBaileysVersion();
  console.log(`Menggunakan WA v${version.join('.')}, isLatest: ${isLatest}`);

  // 2. Siapkan Auth State
  const { state, saveCreds } = await useMultiFileAuthState(AUTH_DIR);

  // 3. Konfigurasi Socket
  const sockConfig = {
    version,
    logger: pino({ level: "silent" }), // Ubah ke 'debug' jika ingin melihat log detail error
    printQRInTerminal: !USE_PAIRING_CODE, // Matikan QR di terminal jika pakai Pairing Code
    auth: {
        creds: state.creds,
        // Caching kunci agar proses decrypt pesan lebih cepat
        keys: makeCacheableSignalKeyStore(state.keys, pino({ level: "fatal" })),
    },
    // Browser config penting agar QR muncul!
    browser: Browsers.macOS("Chrome"), 
    generateHighQualityLinkPreview: true,
    connectTimeoutMs: 60000,
  };

  sock = makeWASocket(sockConfig);

  // --- LOGIKA PAIRING CODE (SOLUSI QR TIDAK MUNCUL) ---
  if (USE_PAIRING_CODE && !sock.authState.creds.registered) {
    if(!PHONE_NUMBER) {
        console.error("❌ ERROR: PHONE_NUMBER wajib diisi di .env jika menggunakan Pairing Code!");
    } else {
        // Tunggu 3 detik agar socket siap
        setTimeout(async () => {
            try {
                const code = await sock.requestPairingCode(PHONE_NUMBER);
                lastPairingCode = code;
                console.log(`\n================================`);
                console.log(`KODE PAIRING ANDA: ${code}`);
                console.log(`================================\n`);
                connectionStatus.message = `Pairing Code: ${code}`;
            } catch (err) {
                console.error("Gagal request pairing code:", err);
            }
        }, 3000);
    }
  }

  // --- EVENT LISTENER ---

  sock.ev.on("connection.update", async (update) => {
    const { connection, lastDisconnect, qr } = update;

    // Handle QR Code (Jika tidak pakai Pairing Code)
    if (qr && !USE_PAIRING_CODE) {
      console.log("Scan QR Code dibawah ini:");
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

    // Handle Status Koneksi
    if (connection === "close") {
      const statusCode = lastDisconnect?.error?.output?.statusCode;
      // 401 = Logged Out (Sesi dihapus dari HP), selain itu Reconnect
      const shouldReconnect = statusCode !== DisconnectReason.loggedOut;

      connectionStatus = {
        connected: false,
        qrAvailable: false,
        message: `Connection closed. ${shouldReconnect ? "Reconnecting..." : "Logged out."}`,
      };

      console.log(`Koneksi terputus (Status: ${statusCode}). Reconnect: ${shouldReconnect}`);

      if (shouldReconnect) {
        // Hapus socket lama biar memory bersih
        setTimeout(connectToWhatsApp, 2000); // Delay dikit biar ga spam
      } else {
        console.log("Sesi berakhir (Logged Out). Menghapus kredensial...");
        deleteAuthFolder();
        // Jangan auto restart kalau logout manual, tunggu trigger manual atau restart server
      }
    } else if (connection === "open") {
      console.log("✅ Berhasil terhubung ke WhatsApp!");
      lastQRCodeBase64 = null;
      lastQRCodeTimestamp = null;
      lastPairingCode = null;
      connectionStatus = {
        connected: true,
        qrAvailable: false,
        message: "Connected",
      };
    }
  });

  // Simpan kredensial setiap ada update
  sock.ev.on("creds.update", saveCreds);
}

/**
 * ==========================================
 * HELPER FUNCTIONS
 * ==========================================
 */

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

const formatPhoneNumber = (number) => {
  let formatted = number.toString().replace(/\D/g, "");
  if (formatted.startsWith("0")) {
    formatted = "62" + formatted.slice(1);
  } else if (formatted.startsWith("8")) {
    formatted = "62" + formatted;
  }
  return `${formatted}@s.whatsapp.net`;
};

const validateToken = (req, res, next) => {
  const token = req.body.token || req.query.token || req.headers["x-api-key"];
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
    
    // Cek onWhatsApp dengan timeout manual agar tidak hang
    let onWaResult;
    try {
        const result = await Promise.race([
            sock.onWhatsApp(formattedNomor),
            new Promise((_, reject) => setTimeout(() => reject(new Error("Timeout")), 5000))
        ]);
        [onWaResult] = result || [];
    } catch (e) {
        // Jika timeout/error, asumsikan nomor ada dan coba kirim
        onWaResult = { exists: true, jid: formattedNomor };
    }

    const jid = onWaResult?.jid || formattedNomor;
    
    if (gambar_url) {
        await sock.sendMessage(jid, {
            image: { url: gambar_url },
            caption: pesan,
        });
    } else {
        await sock.sendMessage(jid, { text: pesan });
    }

    return res.status(200).json({ success: true, message: "Pesan terkirim" });
  } catch (error) {
    console.error("Error kirim pesan:", error);
    res.status(500).json({ success: false, message: "Gagal kirim pesan", error: error.message });
  }
});

// 2. Reset Sesi
app.post("/reset", validateToken, async (req, res) => {
  console.log("Permintaan reset sesi diterima...");
  try {
    if (sock) {
        sock.end(undefined); // Tutup socket dengan benar
        sock = null;
    }
  } catch (e) {}

  deleteAuthFolder();
  
  // Reset Variables
  lastQRCodeBase64 = null;
  lastPairingCode = null;
  connectionStatus = { connected: false, message: "Resetting..." };

  setTimeout(() => connectToWhatsApp(), 2000);

  res.json({ success: true, message: "Sesi direset. Tunggu QR/Pairing Code baru." });
});

// 3. Status & QR/Pairing Code
app.get("/status", validateToken, (req, res) => {
  res.json({
    success: true,
    data: {
        ...connectionStatus,
        mode: USE_PAIRING_CODE ? "Pairing Code" : "QR Code"
    },
    qr: lastQRCodeBase64 ? {
        base64: lastQRCodeBase64,
        timestamp: lastQRCodeTimestamp
    } : null,
    pairing_code: lastPairingCode // Tampilkan pairing code di API response
  });
});

// 4. Kalkulator Zakat (Tetap sama)
app.get("/kalkulator-details", async (req, res) => {
    try {
        const { data } = await axios.get("https://sobatberbagi.com/kalkulator-zakat/emas", { timeout: 10000 });
        const $ = cheerio.load(data);
        const parseValue = (text) => {
            if (!text) return 0;
            return parseInt(text.replace(/[^0-9]/g, ""), 10) || 0;
        };
        const getPrice = (label) => {
            return parseValue($(`#note-zakat ul li:contains('${label}') strong`).text());
        };

        const hargaEmas = getPrice("Harga emas per gram") || 1400000; // Fallback jika 0
        
        res.json({
            success: true,
            source: "sobatberbagi.com",
            data: {
                harga_emas_per_gram: hargaEmas,
                harga_gabah_per_kg: getPrice("Harga gabah per kg") || 7000,
                nisab_maal_tahunan: getPrice("Nishab 85 gram per tahun") || (85 * hargaEmas),
                nisab_maal_bulanan: getPrice("Nishab 85 gram per bulan") || ((85 * hargaEmas) / 12),
                nisab_pertanian: getPrice("Nishab Zakat Pertanian") || (653 * 7000),
            },
        });
    } catch (error) {
        res.status(500).json({ success: false, message: "Scraping failed", error: error.message });
    }
});

// Start Server
app.listen(PORT, () => {
  console.log(`\n🚀 Server API WhatsApp berjalan di port ${PORT}`);
  console.log(`⚙️  Mode: ${USE_PAIRING_CODE ? 'Pairing Code (Cek Console/API)' : 'QR Code'}`);
  connectToWhatsApp();
});