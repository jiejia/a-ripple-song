<?php
/**
 * Compile .po files to .mo files
 * Usage: php compile-mo.php
 */

function compile_po_to_mo($po_file) {
    $mo_file = str_replace('.po', '.mo', $po_file);

    if (!file_exists($po_file)) {
        echo "Error: PO file not found: $po_file\n";
        return false;
    }

    // Use msgfmt if available (from gettext tools)
    $msgfmt_cmd = "msgfmt -o " . escapeshellarg($mo_file) . " " . escapeshellarg($po_file);
    exec($msgfmt_cmd . " 2>&1", $output, $return_code);

    if ($return_code === 0) {
        echo "Successfully compiled: $mo_file\n";
        return true;
    } else {
        echo "Failed to compile with msgfmt, trying PHP fallback...\n";
        return compile_po_to_mo_php($po_file, $mo_file);
    }
}

function compile_po_to_mo_php($po_file, $mo_file) {
    // Simple PHP implementation to create .mo file
    $entries = parse_po_file($po_file);
    if (empty($entries)) {
        echo "Error: Could not parse PO file\n";
        return false;
    }

    return write_mo_file($mo_file, $entries);
}

function parse_po_file($po_file) {
    $content = file_get_contents($po_file);
    $entries = [];
    $current_entry = null;
    $in_msgid = false;
    $in_msgstr = false;
    $in_msgctxt = false;

    $lines = explode("\n", $content);

    foreach ($lines as $line) {
        $line = trim($line);

        if (empty($line) || strpos($line, '#') === 0) {
            if ($current_entry && ($in_msgid || $in_msgstr)) {
                $entries[] = $current_entry;
                $current_entry = null;
                $in_msgid = false;
                $in_msgstr = false;
                $in_msgctxt = false;
            }
            continue;
        }

        if (strpos($line, 'msgid "') === 0) {
            if ($current_entry && $in_msgid) {
                $entries[] = $current_entry;
            }
            $current_entry = ['msgid' => '', 'msgstr' => '', 'msgctxt' => ''];
            $in_msgid = true;
            $in_msgstr = false;
            $in_msgctxt = false;
            $current_entry['msgid'] .= substr($line, 7, -1);
        } elseif (strpos($line, 'msgstr "') === 0) {
            $in_msgid = false;
            $in_msgstr = true;
            $in_msgctxt = false;
            $current_entry['msgstr'] .= substr($line, 8, -1);
        } elseif (strpos($line, 'msgctxt "') === 0) {
            $in_msgctxt = true;
            $current_entry['msgctxt'] .= substr($line, 9, -1);
        } elseif (strpos($line, '"') === 0 && ($in_msgid || $in_msgstr || $in_msgctxt)) {
            $content = substr($line, 1, -1);
            if ($in_msgid) {
                $current_entry['msgid'] .= $content;
            } elseif ($in_msgstr) {
                $current_entry['msgstr'] .= $content;
            } elseif ($in_msgctxt) {
                $current_entry['msgctxt'] .= $content;
            }
        }
    }

    if ($current_entry && ($in_msgid || $in_msgstr)) {
        $entries[] = $current_entry;
    }

    return $entries;
}

function write_mo_file($mo_file, $entries) {
    // Simple MO file format implementation
    $output = '';

    // MO file header
    $output .= pack('V', 0x950412de); // magic number
    $output .= pack('V', 0); // version
    $output .= pack('V', count($entries)); // number of entries
    $output .= pack('V', 28); // offset of key table
    $output .= pack('V', 28 + count($entries) * 8); // offset of value table
    $output .= pack('V', 0); // size of hashing table
    $output .= pack('V', 0); // offset of hashing table

    // Build key and value tables
    $key_table = '';
    $value_table = '';
    $key_offsets = [];
    $value_offsets = [];

    $key_start = 28 + count($entries) * 16;
    $value_start = $key_start;

    foreach ($entries as $entry) {
        $key = $entry['msgid'];
        $value = $entry['msgstr'];

        $key_offsets[] = strlen($key);
        $key_offsets[] = $key_start + strlen($key_table);
        $key_table .= $key . "\0";

        $value_offsets[] = strlen($value);
        $value_offsets[] = $value_start + strlen($value_table) + strlen($key_table);
        $value_table .= $value . "\0";
    }

    // Write the complete file
    $output .= $key_table;
    $output .= $value_table;

    // Update offsets in header
    $complete_output = pack('V', 0x950412de); // magic number
    $complete_output .= pack('V', 0); // version
    $complete_output .= pack('V', count($entries)); // number of entries
    $complete_output .= pack('V', 28); // offset of key table
    $complete_output .= pack('V', 28 + count($entries) * 8); // offset of value table
    $complete_output .= pack('V', 0); // size of hashing table
    $complete_output .= pack('V', 0); // offset of hashing table

    // Add key entries
    foreach ($key_offsets as $offset) {
        $complete_output .= pack('V', $offset);
    }

    // Add value entries
    foreach ($value_offsets as $offset) {
        $complete_output .= pack('V', $offset);
    }

    $complete_output .= $key_table;
    $complete_output .= $value_table;

    if (file_put_contents($mo_file, $complete_output)) {
        echo "Successfully compiled: $mo_file\n";
        return true;
    } else {
        echo "Failed to write MO file: $mo_file\n";
        return false;
    }
}

// Compile all PO files in the lang directory
$lang_dir = __DIR__ . '/resources/lang/';
$po_files = glob($lang_dir . '*.po');

echo "Found " . count($po_files) . " PO files to compile...\n";

foreach ($po_files as $po_file) {
    compile_po_to_mo($po_file);
}

echo "Compilation complete!\n";