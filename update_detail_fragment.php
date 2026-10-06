<?php

$file = 'resources/views/dispensasi/_detail-fragment.blade.php';
$content = file_get_contents($file);

$search = '        <x-ui.field-static label="Jam ke-" value="{{ $dispensasi->labelJam() }}" />';

$replace = "        <x-ui.field-static label=\"Jam ke-\" value=\"{{ \$dispensasi->labelJam() }}\" />
        <x-ui.field-static label=\"Jenis\" value=\"{{ str_replace('_', ' ', Str::title(\$dispensasi->jenis)) }}\" />";

$content = str_replace($search, $replace, $content);
file_put_contents($file, $content);
echo "Updated detail fragment\n";
