@php
    $i18nPath = lang_path(app()->getLocale() . '.json');
    $i18n = \Illuminate\Support\Facades\File::exists($i18nPath)
        ? json_decode(\Illuminate\Support\Facades\File::get($i18nPath), true)
        : [];
@endphp
<script>
    window.I18N = @json($i18n);
    window.t = function (key, params) {
        params = params || {};
        let line = window.I18N[key] || key;

        Object.keys(params).forEach(function (k) {
            const value = params[k];
            const placeholders = [
                ':' + k,
                ':' + k.charAt(0).toUpperCase() + k.slice(1),
                ':' + k.toUpperCase(),
            ];

            placeholders.forEach(function (placeholder) {
                line = line.split(placeholder).join(value);
            });
        });

        return line;
    };
</script>