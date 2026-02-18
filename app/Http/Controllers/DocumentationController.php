<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class DocumentationController extends Controller
{
    /**
     * Menampilkan panduan penggunaan sistem untuk pengguna (dari docs/PANDUAN-PENGGUNAAN-SISTEM.md).
     */
    public function __invoke(): \Illuminate\View\View|\Illuminate\Contracts\View\View
    {
        $path = base_path('docs/PANDUAN-PENGGUNAAN-SISTEM.md');

        if (! File::exists($path)) {
            $content = '<p class="text-zinc-500 dark:text-zinc-400">Panduan penggunaan tidak ditemukan.</p>';
        } else {
            $markdown = File::get($path);
            $content = Str::markdown($markdown);
        }

        return view('docs.show', [
            'title' => __('Panduan Penggunaan'),
            'content' => $content,
        ]);
    }
}
