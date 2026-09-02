<?php

namespace Tests\Feature;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class LocalizationSyncTest extends TestCase
{
    /**
     * Task 54 (revisited): every translation key referenced by the Blade views —
     * server-side __() and client-side t() — must exist in en.json.
     */
    public function test_all_view_translation_keys_exist_in_en_json(): void
    {
        $en = json_decode(File::get(lang_path('en.json')), true);
        $this->assertIsArray($en);

        $keys = $this->extractTranslationKeysFromViews();

        $this->assertNotEmpty($keys, 'No translation keys found in views — extraction regex broke?');

        $missing = [];
        foreach ($keys as $key) {
            if (! array_key_exists($key, $en)) {
                $missing[] = $key;
            }
        }

        $this->assertSame(
            [],
            $missing,
            'Translation keys used in views but missing from en.json: ' . implode(' | ', $missing)
        );
    }

    /**
     * Every locale file must define exactly the same key set as en.json —
     * no missing translations, no stale keys.
     */
    public function test_locale_files_have_identical_key_sets_as_en(): void
    {
        $en = json_decode(File::get(lang_path('en.json')), true);

        foreach (['bn', 'es'] as $locale) {
            $translations = json_decode(File::get(lang_path("{$locale}.json")), true);

            $missing = array_diff_key($en, $translations);
            $extra = array_diff_key($translations, $en);

            $this->assertSame(
                [],
                array_keys($missing),
                "Keys present in en.json but missing from {$locale}.json: " . implode(' | ', array_keys($missing))
            );

            $this->assertSame(
                [],
                array_keys($extra),
                "Keys present in {$locale}.json but not in en.json: " . implode(' | ', array_keys($extra))
            );
        }
    }

    /**
     * End-to-end proof of the wiring: switching the locale to Bangla must
     * render Bangla text on the login page.
     */
    public function test_bangla_locale_renders_translated_login_page(): void
    {
        $response = $this->withSession(['locale' => 'bn'])->get('/login');

        $response->assertStatus(200);
        $response->assertSee('লগইন');
        $response->assertSee('ইমেইল');
        $response->assertSee('পাসওয়ার্ড');
    }

    /**
     * End-to-end proof of the wiring: switching the locale to Spanish must
     * render Spanish text on the customer products page.
     */
    public function test_spanish_locale_renders_translated_products_page(): void
    {
        $response = $this->withSession(['locale' => 'es'])->get('/customer/products');

        $response->assertStatus(200);
        $response->assertSee('Explorar Comestibles');
        $response->assertSee('Estado del Inventario');
    }

    /**
     * The legacy keyed translation files must be gone after the JSON migration.
     */
    public function test_legacy_keyed_translation_files_are_removed(): void
    {
        foreach (['en', 'bn', 'es', 'fr'] as $locale) {
            $this->assertFileDoesNotExist(lang_path("{$locale}/messages.php"));
        }

        $this->assertDirectoryDoesNotExist(lang_path('fr'));
    }

    /**
     * Extract every literal key passed to __() or t() across all Blade views.
     * Dynamic keys such as t(capitalizeStatus(...)) are skipped on purpose —
     * the keys they resolve to (Pending, Confirmed, ...) appear as literals elsewhere.
     *
     * @return array<int, string>
     */
    private function extractTranslationKeysFromViews(): array
    {
        $files = new Filesystem;
        $views = resource_path('views');
        $keys = [];

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($views)
        );

        /** @var \SplFileInfo $file */
        foreach ($iterator as $file) {
            if (! $file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $content = $files->get($file->getPathname());

            // Match __('Key'), __("Key"), t('Key'), t("Key") — but not split(, parseInt(, etc.
            preg_match_all('/(?<![A-Za-z0-9_$])(?:__|t)\(\s*([\'"])(.*?)\1/', $content, $matches);

            foreach ($matches[2] as $key) {
                if ($key !== '') {
                    $keys[] = $key;
                }
            }
        }

        return array_values(array_unique($keys));
    }
}