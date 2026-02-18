<x-layouts::app.sidebar :title="$title">
    <flux:main>
        <div class="p-6">
            <div class="mb-6">
                <flux:heading size="xl">{{ __('Panduan Penggunaan Sistem') }}</flux:heading>
                <flux:text class="mt-1 text-zinc-500 dark:text-zinc-400">
                    {{ __('Cara menggunakan fitur-fitur sistem untuk pengguna apotek.') }}
                </flux:text>
            </div>

            <flux:card class="p-6 md:p-8">
                <div class="docs-content text-zinc-700 dark:text-zinc-300">
                    {!! $content !!}
                </div>
            </flux:card>
        </div>

        <style>
            .docs-content h1 { font-size: 1.5rem; font-weight: 700; margin-top: 1.5rem; margin-bottom: 0.75rem; color: inherit; }
            .docs-content h1:first-child { margin-top: 0; }
            .docs-content h2 { font-size: 1.25rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; color: inherit; border-bottom: 1px solid rgb(228 228 231); padding-bottom: 0.25rem; }
            .dark .docs-content h2 { border-color: rgb(63 63 70); }
            .docs-content h3 { font-size: 1.1rem; font-weight: 600; margin-top: 1rem; margin-bottom: 0.5rem; color: inherit; }
            .docs-content p { margin-bottom: 0.75rem; line-height: 1.6; }
            .docs-content ul, .docs-content ol { margin-bottom: 0.75rem; padding-left: 1.5rem; }
            .docs-content li { margin-bottom: 0.25rem; }
            .docs-content table { width: 100%; border-collapse: collapse; margin-top: 0.5rem; margin-bottom: 1rem; font-size: 0.875rem; }
            .docs-content th, .docs-content td { border: 1px solid rgb(228 228 231); padding: 0.5rem 0.75rem; text-align: left; }
            .dark .docs-content th, .dark .docs-content td { border-color: rgb(63 63 70); }
            .docs-content th { font-weight: 600; background: rgb(244 244 245); }
            .dark .docs-content th { background: rgb(39 39 42); }
            .docs-content code { font-size: 0.875em; padding: 0.15rem 0.35rem; border-radius: 0.25rem; background: rgb(244 244 245); color: rgb(113 113 122); }
            .dark .docs-content code { background: rgb(39 39 42); color: rgb(161 161 170); }
            .docs-content pre { margin-bottom: 1rem; padding: 0.75rem 1rem; border-radius: 0.5rem; overflow-x: auto; background: rgb(244 244 245); font-size: 0.8125rem; line-height: 1.5; }
            .dark .docs-content pre { background: rgb(39 39 42); }
            .docs-content pre code { padding: 0; background: none; color: inherit; }
            .docs-content hr { border: 0; border-top: 1px solid rgb(228 228 231); margin: 1.5rem 0; }
            .dark .docs-content hr { border-color: rgb(63 63 70); }
            .docs-content strong { font-weight: 600; }
        </style>
    </flux:main>
</x-layouts::app.sidebar>
