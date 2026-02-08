<?php
declare(strict_types=1);

function content_store_path(string $name): string
{
    $base = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'data';
    if (!is_dir($base)) {
        @mkdir($base, 0775, true);
    }
    return $base . DIRECTORY_SEPARATOR . $name;
}

function load_index_sections(): array
{
    $path = content_store_path('index.sections.json');
    if (!is_file($path)) {
        return ['sections' => []];
    }

    $raw = file_get_contents($path);
    $data = json_decode($raw ?: '', true);
    if (!is_array($data) || !isset($data['sections']) || !is_array($data['sections'])) {
        return ['sections' => []];
    }
    return $data;
}

function save_index_sections(array $data): void
{
    $path = content_store_path('index.sections.json');
    $tmp = $path . '.tmp';
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($json === false) {
        throw new RuntimeException('Nelze serializovat JSON.');
    }
    if (file_put_contents($tmp, $json, LOCK_EX) === false) {
        throw new RuntimeException('Nelze zapsat JSON (tmp).');
    }
    if (!@rename($tmp, $path)) {
        @unlink($tmp);
        throw new RuntimeException('Nelze uložit JSON (rename).');
    }
}

function find_section_index(array $sections, string $id): int
{
    foreach ($sections as $i => $s) {
        if (is_array($s) && ($s['id'] ?? null) === $id) {
            return (int)$i;
        }
    }
    return -1;
}
