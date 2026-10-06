<?php

$file = 'app/Http/Controllers/DispensasiController.php';
$content = file_get_contents($file);

$search = "            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);";

$replace = "            'jenis' => ['required', 'string'],
            'surat' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:4096'],
        ]);

        if (in_array(\$data['jenis'], ['sakit', 'izin_keluar', 'izin_terlambat']) && !\$request->hasFile('surat')) {
            return back()->withInput()->withErrors(['surat' => 'Bukti surat wajib diunggah untuk jenis dispensasi ini.']);
        }
";
$content = str_replace($search, $replace, $content);

$search_loop = "                    'status_piket' => 'approved',
                    'piket_id' => auth()->id(),
                ]);
                \$dispensasi->segarkanStatusAkhir();";

$replace_loop = "                    'status_piket' => 'approved',
                    'piket_id' => auth()->id(),
                    'status_waka' => \$dataPengajuan['jenis'] === 'lomba' ? 'approved' : 'pending',
                ]);
                \$dispensasi->segarkanStatusAkhir();";

$content = str_replace($search_loop, $replace_loop, $content);
file_put_contents($file, $content);
echo "Updated DispensasiController.php\n";
