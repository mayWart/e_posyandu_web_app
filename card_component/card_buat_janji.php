<div id="modalJanji" class="fixed inset-0 z-50 hidden overflow-y-auto" aria-labelledby="modal-title" role="dialog" aria-modal="true">
    
    <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div class="fixed inset-0 bg-[#1D3428] bg-opacity-90 transition-opacity backdrop-blur-sm" aria-hidden="true" onclick="toggleModalJanji()"></div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <div class="inline-block align-bottom bg-[#2d4a38] rounded-2xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full border border-[#D0F246]/30">
            
            <div class="bg-[#1D3428] px-6 py-4 flex justify-between items-center border-b border-[#D0F246]/20">
                <h3 class="text-lg leading-6 font-bold text-[#D0F246] flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    Buat Janji Imunisasi
                </h3>
                <button type="button" onclick="toggleModalJanji()" class="text-[#D0F246] hover:text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <form method="POST" action="">
                <div class="px-6 py-6 space-y-5">
                    
                    <div>
                        <label class="block text-sm font-medium text-[#D0F246] mb-1">Pilih Anak</label>
                        <div class="relative">
                            <select name="balita_id" required class="block w-full pl-3 pr-10 py-2 text-base border-[#154620] focus:outline-none focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm rounded-lg border bg-[#1D3428] text-white">
                                <option value="" disabled selected>-- Pilih Buah Hati --</option>
                                <?php 
                                // Reset pointer data balita agar bisa dibaca ulang
                                mysqli_data_seek($queryAnak, 0);
                                while($b = mysqli_fetch_assoc($queryAnak)): 
                                ?>
                                    <option value="<?= $b['id']; ?>" class="bg-[#1D3428] text-white"><?= $b['nama_balita']; ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#D0F246] mb-1">Rencana Imunisasi</label>
                        <select name="imunisasi_id" required class="block w-full pl-3 pr-10 py-2 text-base border-[#154620] focus:outline-none focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm rounded-lg border bg-[#1D3428] text-white">
                            <option value="" disabled selected class="bg-[#1D3428] text-white">-- Pilih Jenis Vaksin --</option>
                            <?php 
                            // Query Master Imunisasi harus dijalankan di dashboard
                            if(isset($queryImunisasi)) {
                                mysqli_data_seek($queryImunisasi, 0);
                                while($i = mysqli_fetch_assoc($queryImunisasi)): 
                            ?>
                                <option value="<?= $i['id']; ?>" class="bg-[#1D3428] text-white"><?= $i['nama_imunisasi']; ?> (Usia: <?= $i['usia_wajib_bulan']; ?> bln)</option>
                            <?php 
                                endwhile; 
                            }
                            ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#D0F246] mb-1">Tanggal Rencana Datang</label>
                        <input type="date" name="tanggal_rencana" required class="block w-full border border-[#154620] rounded-lg shadow-sm py-2 px-3 bg-[#1D3428] text-white focus:outline-none focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm">
                        <p class="mt-1 text-xs text-gray-400">Sesuaikan dengan jadwal buka Posyandu.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-[#D0F246] mb-1">Catatan (Opsional)</label>
                        <textarea name="catatan" rows="2" class="block w-full border border-[#154620] rounded-lg shadow-sm py-2 px-3 bg-[#1D3428] text-white focus:outline-none focus:ring-[#D0F246] focus:border-[#D0F246] sm:text-sm" placeholder="Contoh: Datang pagi jam 8"></textarea>
                    </div>

                </div>

                <div class="bg-[#1D3428] px-6 py-4 flex flex-row-reverse gap-3 border-t border-[#D0F246]/20">
                    <button type="submit" name="buat_janji" class="w-full inline-flex justify-center rounded-xl border border-[#D0F246] shadow-sm px-4 py-2 bg-[#D0F246] text-base font-medium text-[#1D3428] hover:bg-[#bce038] focus:outline-none sm:w-auto sm:text-sm">
                        Buat Janji
                    </button>
                    <button type="button" onclick="toggleModalJanji()" class="mt-3 w-full inline-flex justify-center rounded-xl border border-[#154620] shadow-sm px-4 py-2 bg-[#2d4a38] text-base font-medium text-[#D0F246] hover:bg-[#154620] focus:outline-none sm:mt-0 sm:w-auto sm:text-sm">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleModalJanji() {
        const modal = document.getElementById('modalJanji');
        modal.classList.toggle('hidden');
    }
</script>