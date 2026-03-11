<?php
declare(strict_types=1);

/**
 * UI -- F3 custom-tag component system
 *
 * Funzionamento:
 *   1. Ogni tag ha un file HTML in /components/<tag-name>.html
 *   2. Il file usa {prop:key}, {prop:key|raw}, {prop:key?} per i prop passati via attributo
 *   3. <slot></slot> nel file viene sostituito con il contenuto interno del tag
 *   4. Tutto avviene a compile-time (prima cache F3), zero overhead a runtime
 *
 * Sintassi nei file componente:
 *   {prop:key}        -> valore escaped del prop (per testo e attributi HTML)
 *   {prop:key|raw}    -> valore raw (per attributi HTML booleani: required, disabled...)
 *   {prop:key?}       -> espressione PHP booleana (per <?php if ({prop:key?}): ?>)
 *   <slot></slot>     -> contenuto interno del tag
 */
class UI
{
    private static string $path = '';

    /**
     * Genera un'istruzione PHP echo con htmlspecialchars.
     * Usa doppi apici per la stringa di encoding: evita escape di virgolette singole
     * che alcuni tool di editing/scrittura possono corrompere.
     */
    private static function echoEscaped(string $phpExpr): string
    {
        return '<?php echo htmlspecialchars((string)(' . $phpExpr . '),ENT_QUOTES,"UTF-8"); ?>';
    }

    /**
     * Converte un valore misto (token F3 + testo letterale) in un'espressione PHP valida.
     *
     * Il problema: $tpl->token() funziona su un singolo token puro come {{ @BASE }},
     * ma se il valore e' misto come "{{@BASE}}/utenti" restituisce "$BASE/utenti"
     * che e' PHP invalido (divisione invece di concatenazione).
     *
     * Questa funzione spezza il valore in parti, converte ogni {{ @expr }} con token(),
     * e ricombina tutto con l'operatore di concatenazione PHP (.):
     *
     *   "{{@BASE}}/utenti"          -> ($BASE).'/utenti'
     *   "{{ @BASE }}/utenti/{{ @id }}" -> ($BASE).'/utenti/'.($id)
     *   "testo fisso"               -> 'testo fisso'
     *   "{{ @pageTitle }}"          -> ($pageTitle)
     */
    private static function tokenize(string $val, \Template $tpl): string
    {
        $parts    = preg_split('/(\{\{.+?\}\})/', $val, -1, PREG_SPLIT_DELIM_CAPTURE);
        $phpParts = [];
        foreach ($parts as $part) {
            if ($part === '') continue;
            if (str_starts_with($part, '{{')) {
                $phpParts[] = '(' . $tpl->token($part) . ')';
            } else {
                $phpParts[] = "'" . addslashes($part) . "'";
            }
        }
        return implode('.', $phpParts ?: ["''"]);
    }

    public static function register(string $componentsPath = ''): void
    {
        self::$path = $componentsPath ?: dirname(__DIR__) . '/components';

        $t = \Template::instance();
        $t->extend('ui-layout',    [self::class, 'layout']);
        $t->extend('ui-navbar',    [self::class, 'navbar']);
        $t->extend('ui-nav-link',  [self::class, 'navLink']);
        $t->extend('ui-container', [self::class, 'container']);
        $t->extend('ui-grid',      [self::class, 'grid']);
        $t->extend('ui-card',      [self::class, 'card']);
        $t->extend('ui-alert',     [self::class, 'alert']);
        $t->extend('ui-form',      [self::class, 'form']);
        $t->extend('ui-input',     [self::class, 'input']);
        $t->extend('ui-textarea',  [self::class, 'textarea']);
        $t->extend('ui-select',    [self::class, 'select']);
        $t->extend('ui-button',    [self::class, 'button']);
        $t->extend('ui-badge',     [self::class, 'badge']);
    }

    // =========================================================================
    // Handlers
    // =========================================================================

    public static function layout(array $node): string    { return self::compile('ui-layout', $node); }
    public static function navbar(array $node): string    { return self::compile('ui-navbar', $node); }
    public static function container(array $node): string { return self::compile('ui-container', $node); }
    public static function card(array $node): string      { return self::compile('ui-card', $node); }
    public static function form(array $node): string      { return self::compile('ui-form', $node); }
    public static function textarea(array $node): string  { return self::compile('ui-textarea', $node); }
    public static function select(array $node): string    { return self::compile('ui-select', $node); }

    public static function navLink(array $node): string
    {
        $a = $node['@attrib'] ?? [];
        if (!isset($a['href']))  $a['href']  = '#';
        if (!isset($a['label'])) $a['label'] = '';
        $node['@attrib'] = $a;
        return self::compile('ui-nav-link', $node);
    }

    public static function grid(array $node): string
    {
        $a   = $node['@attrib'] ?? [];
        $map = [
            '1' => 'grid-cols-1',
            '2' => 'grid-cols-1 md:grid-cols-2',
            '3' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-3',
            '4' => 'grid-cols-1 md:grid-cols-2 lg:grid-cols-4',
        ];
        $a['_gridCls']   = $map[$a['cols'] ?? '2'] ?? $map['2'];
        $node['@attrib'] = $a;
        return self::compile('ui-grid', $node);
    }

    public static function alert(array $node): string
    {
        $a      = $node['@attrib'] ?? [];
        $type   = $a['type'] ?? 'info';
        $styles = [
            'success' => ['bg-green-50 border-green-400 text-green-800', '&#10003;', 'text-green-700 font-bold'],
            'error'   => ['bg-red-50 border-red-400 text-red-800',       '&#10007;', 'text-red-700 font-bold'],
            'warning' => ['bg-yellow-50 border-yellow-400 text-yellow-800', '&#9888;', 'text-yellow-700 font-bold'],
            'info'    => ['bg-blue-50 border-blue-400 text-blue-800',    '&#8505;',  'text-blue-700 font-bold'],
        ];
        [$cls, $icon, $iconCls] = $styles[$type] ?? $styles['info'];
        $a['_cls']       = $cls;
        $a['_icon']      = $icon;
        $a['_iconCls']   = $iconCls;
        $node['@attrib'] = $a;
        return self::compile('ui-alert', $node);
    }

    public static function input(array $node): string
    {
        $a     = $node['@attrib'] ?? [];
        $label = $a['label'] ?? '';
        if (!isset($a['name']))        $a['name']        = strtolower(preg_replace('/[^a-z0-9]+/i', '_', $label));
        if (!isset($a['id']))          $a['id']          = $a['name'];
        if (!isset($a['type']))        $a['type']        = 'text';
        if (!isset($a['placeholder'])) $a['placeholder'] = $label;
        $node['@attrib'] = $a;
        return self::compile('ui-input', $node);
    }

    public static function button(array $node): string
    {
        $a      = $node['@attrib'] ?? [];
        $color  = $a['color'] ?? 'blue';
        $colors = [
            'blue'  => 'bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white border-transparent',
            'green' => 'bg-green-600 hover:bg-green-700 focus:ring-green-500 text-white border-transparent',
            'red'   => 'bg-red-600 hover:bg-red-700 focus:ring-red-500 text-white border-transparent',
            'gray'  => 'bg-gray-500 hover:bg-gray-600 focus:ring-gray-400 text-white border-transparent',
            'white' => 'bg-white hover:bg-gray-50 focus:ring-gray-300 text-gray-700 border-gray-300',
        ];
        $a['_colorCls']  = $colors[$color] ?? $colors['blue'];
        $a['type']       = $a['type'] ?? 'submit';
        $node['@attrib'] = $a;
        return self::compile('ui-button', $node);
    }

    public static function badge(array $node): string
    {
        $a      = $node['@attrib'] ?? [];
        $color  = $a['color'] ?? 'gray';
        $colors = [
            'blue'   => 'bg-blue-100 text-blue-800',
            'green'  => 'bg-green-100 text-green-800',
            'red'    => 'bg-red-100 text-red-800',
            'yellow' => 'bg-yellow-100 text-yellow-800',
            'gray'   => 'bg-gray-100 text-gray-700',
        ];
        $a['_colorCls']  = $colors[$color] ?? $colors['gray'];
        $node['@attrib'] = $a;
        return self::compile('ui-badge', $node);
    }

    // =========================================================================
    // Motore di compilazione
    // =========================================================================

    /**
     * Compila un file componente in codice PHP compatibile con F3.
     *
     * IMPORTANTE: non chiama parse() sul file componente.
     * parse() sovrascrive $this->tree del Template engine e corromperebbe
     * la compilazione del template padre. I componenti usano PHP puro,
     * non tag F3 (<check>, <loop>).
     *
     * Pipeline:
     *   1. Compila $node[0] (inner content) PRIMA di qualsiasi parse()
     *   2. Legge components/<tag>.html e sostituisce {prop:*}
     *   3. Converte {{ @var }} residui in PHP con token() senza parse()
     *   4. Sostituisce <slot></slot> con l'inner gia compilato
     */
    private static function compile(string $tag, array $node): string
    {
        $attrib = $node['@attrib'] ?? [];
        $tpl    = \Template::instance();
        $file   = rtrim(self::$path, '/') . '/' . $tag . '.html';

        if (!file_exists($file)) {
            return '<!-- [UI] componente non trovato: ' . htmlspecialchars($tag, ENT_QUOTES, 'UTF-8') . ' -->';
        }

        // 1. Compila il contenuto interno.
        //
        // STRUTTURA REALE DEL NODO F3 (verificata con json_encode):
        //   $node['@attrib'] => array attributi
        //   $node[0]         => stringa testo / whitespace
        //   $node[1]         => array tag figlio  (es. {'ui-navbar': {...}})
        //   $node[2]         => stringa testo
        //   ...              => alternanza testo/tag alle chiavi intere
        //
        // build() accetta un array di nodi (chiavi intere), esattamente
        // quello che abbiamo filtrando via '@attrib'.
        $children = array_filter($node, static fn($k) => is_int($k), ARRAY_FILTER_USE_KEY);
        $inner    = $tpl->build($children);

        // 2. Leggi e sostituisci i {prop:*}
        $src = self::substituteProps(file_get_contents($file), $attrib, $tpl);

        // 3. Converti {{ @var }} residui in PHP senza usare parse()
        $src = preg_replace_callback(
            '/\{\{(.+?)\}\}/',
            static function (array $m) use ($tpl): string {
                return self::echoEscaped($tpl->token($m[0]));
            },
            $src
        );

        // 4. Inserisci inner al posto di <slot>
        $src = str_replace(['<slot></slot>', '<slot/>'], $inner, $src);

        return $src;
    }

    /**
     * Sostituisce i token {prop:*} nel sorgente di un file componente.
     *
     * F3 passa gli attributi booleani senza valore (es. <ui-nav-link active>)
     * come NULL, non come stringa vuota. Tutti i rami fanno (string)$val
     * prima di qualsiasi confronto per evitare TypeError in PHP 8.
     *
     * Regole:
     *   {prop:key}      Valore HTML-escaped. Se assente o booleano -> ''.
     *                   Se contiene {{ @var }} -> espressione PHP escaped.
     *                   Altrimenti -> stringa HTML-escaped letterale.
     *
     *   {prop:key|raw}  Valore senza escape.
     *                   Se booleano (null / uguale alla chiave) -> nome attributo (es. "required").
     *                   Se contiene {{ @var }} -> <?php echo expr; ?>.
     *                   Altrimenti -> valore letterale.
     *
     *   {prop:key?}     Espressione PHP booleana da usare in <?php if ({prop:key?}): ?>
     *                   Se assente              -> '0'
     *                   Se booleano/stringa vuota -> '1'
     *                   Se contiene {{ @var }}  -> espressione PHP (''!==(expr))
     *                   Altrimenti              -> '1'
     */
    private static function substituteProps(string $src, array $attrib, \Template $tpl): string
    {
        // {prop:key|raw} — valore grezzo, gestisce attributi booleani
        $src = preg_replace_callback(
            '/\{prop:(\w+)\|raw\}/',
            static function (array $m) use ($attrib, $tpl): string {
                $key = $m[1];
                if (!array_key_exists($key, $attrib)) return '';
                $val = (string)($attrib[$key] ?? ''); // null -> '' (attributo booleano F3)
                if ($val === '' || $val === $key) return $key; // boolean attr: required -> "required"
                if (str_contains($val, '{{')) return '<?php echo ' . self::tokenize($val, $tpl) . '; ?>';
                return $val;
            },
            $src
        );

        // {prop:key?} — booleano PHP per <?php if (...): 
        $src = preg_replace_callback(
            '/\{prop:(\w+)\?\}/',
            static function (array $m) use ($attrib, $tpl): string {
                $key = $m[1];
                if (!array_key_exists($key, $attrib)) return '0'; // prop assente -> falso
                $val = (string)($attrib[$key] ?? ''); // null -> '' (attributo booleano F3)
                if ($val === '' || $val === $key) return '1';     // booleano presente -> vero
                if (str_contains($val, '{{')) return "(''!==(".self::tokenize($val, $tpl)."))";
                return '1';
            },
            $src
        );

        // {prop:key} — valore HTML-escaped
        $src = preg_replace_callback(
            '/\{prop:(\w+)\}/',
            static function (array $m) use ($attrib, $tpl): string {
                $key = $m[1];
                $val = (string)($attrib[$key] ?? ''); // null -> '' (attributo booleano F3)
                if ($val === '' || $val === $key) return '';
                if (str_contains($val, '{{')) {
                    return self::echoEscaped(self::tokenize($val, $tpl));
                }
                return htmlspecialchars($val, ENT_QUOTES, 'UTF-8');
            },
            $src
        );

        return $src;
    }
}
