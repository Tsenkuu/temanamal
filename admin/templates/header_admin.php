<?php
// Ambil nama admin dari session untuk ditampilkan
$admin_nama_lengkap = $_SESSION['admin_nama_lengkap'] ?? 'Admin';
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? htmlspecialchars($page_title) . ' - Admin Lazismu' : 'Dashboard Admin'; ?>
    </title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- CSS Kustom untuk Desain Baru -->
    <style>
    body {
        font-family: 'Inter', sans-serif;
        background-color: #f8fafc;
        /* bg-slate-50 */
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }

    ::-webkit-scrollbar-track {
        background: #f1f1f1;
    }

    ::-webkit-scrollbar-thumb {
        background: #d1d5db;
        border-radius: 4px;
    }

    ::-webkit-scrollbar-thumb:hover {
        background: #9ca3af;
    }

    /* Layout Utama */
    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

    .sidebar {
        width: 260px;
        background-color: #ffffff;
        border-right: 1px solid #e5e7eb;
        transition: transform 0.3s ease;
        z-index: 50;
    }

    @media (max-width: 1024px) {
        .sidebar {
            position: fixed;
            top: 0;
            left: 0;
            bottom: 0;
            transform: translateX(-100%);
        }

        .sidebar.is-open {
            transform: translateX(0);
        }
    }

    .main-content {
        flex-grow: 1;
        padding: 1.5rem;
        /* Disesuaikan padding */
        overflow-y: auto;
    }

    .admin-header {
        background-color: #ffffff;
        border-bottom: 1px solid #e5e7eb;
        position: sticky;
        top: 0;
        z-index: 40;
    }

    /* Komponen UI Kustom */
    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }

    .content-card {
        background-color: #ffffff;
        border-radius: 0.75rem;
        padding: 1.5rem;
        box-shadow: 0 1px 3px 0 rgb(0 0 0 / 0.1), 0 1px 2px -1px rgb(0 0 0 / 0.1);
    }

    .card-title {
        font-size: 1.125rem;
        font-weight: 600;
        color: #1f2937;
    }

    .form-label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: 500;
        color: #374151;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        transition: all 0.2s ease;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #fb8201;
        box-shadow: 0 0 0 3px rgba(251, 130, 1, 0.2);
    }

    .form-input-file {
        display: block;
        width: 100%;
        padding: 8px;
        border: 1px solid #d1d5db;
        border-radius: .5rem;
        font-size: .875rem;
    }

    .btn-primary {
        background-color: #fb8201;
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-primary:hover {
        background-color: #f57400;
    }

    .btn-danger {
        background-color: #ef4444; /* bg-red-500 */
        color: white;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }
    .btn-danger:hover {
        background-color: #dc2626; /* bg-red-600 */
    }
    .btn-danger-sm {
        background-color: #fee2e2; /* bg-red-100 */
        color: #b91c1c; /* text-red-700 */
        padding: 0.5rem 1rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-secondary {
        background-color: #e5e7eb;
        color: #374151;
        padding: 0.75rem 1.25rem;
        border-radius: 0.5rem;
        font-weight: 600;
        display: inline-flex;
        align-items: center;
        transition: background-color 0.2s ease;
    }

    .btn-secondary:hover {
        background-color: #d1d5db;
    }

    .btn-icon {
        width: 36px;
        height: 36px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .btn-icon-sm {
        width: 32px;
        height: 32px;
        border-radius: 0.375rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: background-color 0.2s ease;
    }

    .alert-info {
        background-color: #eff6ff;
        color: #1d4ed8;
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
    }

    .alert-success {
        background-color: #f0fdf4;
        color: #166534;
        padding: 1rem;
        border-radius: 0.5rem;
        display: flex;
        align-items: center;
        margin-bottom: 1rem;
    }

    .badge-success {
        background-color: #dcfce7;
        color: #166534;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-warning {
        background-color: #fef9c3;
        color: #854d0e;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-info {
        background-color: #e0f2fe;
        color: #0284c7;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .badge-secondary {
        background-color: #f3f4f6;
        color: #4b5563;
        font-weight: 600;
        font-size: 0.75rem;
        padding: 0.25rem 0.75rem;
        border-radius: 9999px;
    }

    .pagination {
        display: flex;
        justify-content: center;
        gap: 0.5rem;
        margin-top: 1.5rem;
    }

    .pagination a {
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        background-color: #e5e7eb;
        color: #374151;
        text-decoration: none;
    }

    .pagination a:hover {
        background-color: #d1d5db;
    }

    .pagination a.active {
        background-color: #fb8201;
        color: white;
        font-weight: 600;
    }

    /* Komponen Dashboard */
    .header-welcome {
        padding-bottom: 1.5rem;
        border-bottom: 1px solid #e5e7eb;
    }
    .stat-card {
        color: white;
        border-radius: 0.75rem;
        padding: 1.5rem;
        display: flex;
        align-items: center;
        gap: 1rem;
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .stat-icon {
        width: 56px;
        height: 56px;
        border-radius: 9999px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.875rem;
        flex-shrink: 0;
    }
    .stat-label {
        font-size: 0.875rem;
        opacity: 0.9;
    }
    .stat-value {
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.2;
    }
    .quick-action-btn {
        border-radius: 0.75rem;
        padding: 1rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        font-weight: 600;
        text-align: center;
        transition: all 0.2s ease;
    }
    .quick-action-btn:hover {
        transform: translateY(-4px);
        box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1), 0 2px 4px -2px rgb(0 0 0 / 0.1);
    }
    .table-wrapper {
        overflow-x: auto;
    }
    </style>
</head>

<body class="bg-slate-50">
    <div class="admin-layout">
        <?php require_once 'sidebar_admin.php'; ?>
        <div class="flex-1 flex flex-col">
            <header class="admin-header py-3 px-6 flex justify-between items-center">
                <!-- Tombol Hamburger untuk Mobile -->
                <button id="hamburger-btn" class="lg:hidden p-2 rounded-md hover:bg-gray-100">
                    <i class="bi bi-list text-2xl text-gray-600"></i>
                </button>
                <div class="hidden lg:block"></div> <!-- Spacer untuk Desktop -->

                <!-- Menu Profile -->
                <div class="relative">
                    <button id="profile-btn" class="flex items-center gap-2">
                        <span
                            class="font-semibold text-dark-text"><?php echo htmlspecialchars($admin_nama_lengkap); ?></span>
                        <i class="bi bi-chevron-down text-sm"></i>
                    </button>
                    <div id="profile-dropdown"
                        class="absolute right-0 mt-2 w-48 bg-white rounded-md shadow-lg py-1 z-50 hidden">
                        <a href="ganti_sandi.php" class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Ganti
                            Sandi</a>
                        <a href="../logout.php"
                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100">Logout</a>
                    </div>
                </div>
            </header>