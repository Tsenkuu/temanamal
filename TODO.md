# TODO: Implement Opini Functionality

## Admin Side Updates
- [x] Update admin/tambah_berita.php: Add type selection (Berita/Opini)
- [x] Update admin/edit_berita.php: Show and edit the type
- [x] Update admin/kelola_berita.php: Add type filter and display type badges

## Frontend Updates
- [x] Update index.php: Display both berita and opini in the same section
- [x] Update berita.php: Show both types with filtering option
- [x] Update detail_berita.php: Handle both types properly

## Amil Side Updates
- [x] Update amil/tambah_berita.php: Add type selection
- [x] Update amil/kelola_berita.php: Add type filter and display

## Testing
- [ ] Test admin functionality
- [ ] Test frontend display
- [ ] Test amil functionality

---

# TODO: Change Majalah from File Upload to Link

## Database Updates
- [x] Update database table majalah to change nama_file to link

## Admin Side Updates
- [x] Update admin/tambah_majalah.php to use link input instead of file upload
- [x] Update admin/proses_tambah_majalah.php to handle link instead of file upload
- [x] Update admin/kelola_majalah.php to display link instead of file
- [x] Update admin/hapus_majalah.php to remove file deletion logic

## Frontend Updates
- [x] Update baca_majalah.php to redirect to link instead of showing iframe

## Testing
- [ ] Test the changes by adding a new majalah with a link
