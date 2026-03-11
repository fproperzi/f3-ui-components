# F3 UI Components

> Un sistema di componenti UI riusabili per [Fat-Free Framework](https://fatfreeframework.com/), ispirato a **Laravel Blade**, costruito con **Tailwind CSS** e **Alpine.js**.

Scrivi le tue pagine con tag semantici dichiarativi invece di HTML ripetitivo:

```html
<ui-layout title="Gestionale">
    <ui-navbar title="Gestionale">
        <ui-nav-link href="/" label="Dashboard"></ui-nav-link>
        <ui-nav-link href="/utenti" label="Utenti" active></ui-nav-link>
    </ui-navbar>
    <ui-container>
        <ui-alert type="success">Utente salvato correttamente</ui-alert>
        <ui-card title="Nuovo Utente">
            <ui-form action="/salva" method="POST">
                <ui-input label="Nome" name="nome" required></ui-input>
                <ui-input label="Email" name="email" type="email" required></ui-input>
                <ui-button label="Salva"></ui-button>
            </ui-form>
        </ui-card>
    </ui-container>
</ui-layout>
```

---

## Perché questo progetto

Fat-Free Framework ha un ottimo motore di template con supporto ai **custom tag** (`Template::extend()`), ma non esiste un sistema di componenti UI pronto all'uso che sfrutti questa funzionalità.

Questo progetto colma il gap implementando un mini-Blade per F3:

| Blade (Laravel) | F3 UI Components |
|---|---|
| `@extends('layout')` + `@section` | `<ui-layout>` wrappa tutto |
| `<x-card title="...">` | `<ui-card title="...">` |
| `<x-input label="..." />` | `<ui-input label="..." />` |
| `resources/views/components/*.blade.php` | `components/*.html` |
| Compilazione a PHP cached | Compilazione a PHP cached (F3 `tmp/`) |

La differenza chiave rispetto a Blade: i componenti sono **file HTML puri** con una sintassi minimale (`{prop:key}`), non template PHP. Un designer può modificarli senza toccare PHP.

---

## Stack

- **[Fat-Free Framework](https://fatfreeframework.com/) 3.8+** — router e template engine
- **[Tailwind CSS](https://tailwindcss.com/) 3** — utility-first CSS (CLI, non CDN)
- **[Alpine.js](https://alpinejs.dev/) 3** — reattività leggera (alert dismissibili, navbar mobile)

---

## Installazione

```bash
composer require bcosca/fatfree-core
npm install
```

Struttura attesa del progetto:

```
project/
├── .htaccess
├── index.php
├── composer.json
├── package.json
├── input.css
├── tailwind.config.js
├── app/
│   └── UI.php
├── components/
│   ├── ui-layout.html
│   ├── ui-navbar.html
│   ├── ui-nav-link.html
│   ├── ui-container.html
│   ├── ui-grid.html
│   ├── ui-card.html
│   ├── ui-alert.html
│   ├── ui-form.html
│   ├── ui-input.html
│   ├── ui-textarea.html
│   ├── ui-select.html
│   ├── ui-button.html
│   └── ui-badge.html
├── templates/
│   └── demo.html
└── css/
    └── app.css          ← generato da Tailwind CLI
```

---

## Configurazione

**`tailwind.config.js`** — scansiona tutti i file per il purge:

```js
module.exports = {
    content: [
        "./app/**/*.php",
        "./components/**/*.{html,js}",
        "./templates/**/*.html",
    ],
    theme: { extend: {} },
    plugins: []
}
```

**`input.css`** — entrypoint Tailwind:

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

[x-cloak] { display: none !important; }
```

**Build CSS:**

```bash
npm run watch    # sviluppo
npm run build    # produzione (minificato)
```

**`index.php`** — registra i tag nel bootstrap F3:

```php
$f3 = \Base::instance();
$f3->set('UI', 'templates/');
$f3->set('AUTOLOAD', 'app/');

UI::register(__DIR__ . '/components');

$f3->route('GET /', function(\Base $f3) {
    $f3->set('pageTitle', 'Gestionale');
    echo \Template::instance()->render('demo.html');
});
```

---

## Come funziona il motore

`UI.php` registra ogni tag con `Template::extend()`. Quando F3 compila un template, chiama l'handler del tag che esegue questa pipeline:

```
components/ui-card.html
       │
       ▼
   substituteProps()          {prop:title} → valore escaped
                              {prop:title|raw} → valore grezzo
                              {prop:title?} → espressione PHP booleana
       │
       ▼
   preg_replace {{ @var }}    {{ @pageTitle }} → <?php echo ... ?>
       │
       ▼
   str_replace <slot>         <slot></slot> → contenuto interno compilato
       │
       ▼
   codice PHP compilato       messo in cache da F3 (tmp/)
```

**Nota importante:** gli handler non chiamano mai `parse()` sul file componente. `parse()` sovrascrive `$this->tree` interno del Template engine e corromperebbe la compilazione del template padre. I componenti usano `<?php if ... ?>` puro, non tag F3 come `<check>`.

---

## Sintassi nei file componente

### `{prop:key}` — valore escaped

```html
<h2 class="text-lg font-semibold">{prop:title}</h2>
```

Supporta sia valori statici che variabili F3:

```html
<ui-card title="Testo fisso">
<ui-card title="{{ @pageTitle }}">
```

### `{prop:key|raw}` — valore grezzo

Per attributi HTML booleani e entità HTML:

```html
<input {prop:required|raw} {prop:disabled|raw}>
<span>{prop:_icon|raw}</span>   <!-- &#10003; non viene double-escaped -->
```

Attributo booleano F3: `<ui-input required>` passa `null` → `{prop:required|raw}` diventa `required`.

### `{prop:key?}` — condizionale PHP

```html
<?php if ({prop:title?}): ?>
    <div class="border-b">{prop:title}</div>
<?php endif; ?>
```

### `<slot></slot>` — contenuto interno

```html
<div class="card">
    <slot></slot>   <!-- sostituito dal contenuto tra i tag -->
</div>
```

### Prop calcolati (prefisso `_`)

Per logica che non può stare in HTML (selezione classi, lookup array), l'handler PHP inietta prop aggiuntivi con prefisso `_` prima di delegare al file:

```php
// UI.php - handler alert
$styles = ['success' => ['bg-green-50 ...', '&#10003;', 'text-green-700'], ...];
[$cls, $icon, $iconCls] = $styles[$type];
$a['_cls']     = $cls;
$a['_icon']    = $icon;
$a['_iconCls'] = $iconCls;
```

```html
<!-- ui-alert.html -->
<div class="... {prop:_cls}">
    <span class="{prop:_iconCls}">{prop:_icon|raw}</span>
```

---

## Componenti disponibili

### `<ui-layout title>`
Pagina HTML completa. Carica `css/app.css` con `{{ @BASE }}` e Alpine.js CDN.

```html
<ui-layout title="App">
    ...
</ui-layout>
```

### `<ui-navbar title>`
Barra di navigazione responsiva con hamburger menu Alpine.js.

```html
<ui-navbar title="Gestionale">
    <ui-nav-link href="/"       label="Dashboard"></ui-nav-link>
    <ui-nav-link href="/utenti" label="Utenti" active></ui-nav-link>
</ui-navbar>
```

### `<ui-nav-link href label active>`
Link nel navbar. `active` è un attributo booleano.

### `<ui-container>`
Wrapper `container mx-auto px-4 py-6`.

### `<ui-grid cols="1|2|3|4">`
Griglia responsive. Default: `2` colonne.

```html
<ui-grid cols="3">
    <ui-card title="A"></ui-card>
    <ui-card title="B"></ui-card>
    <ui-card title="C"></ui-card>
</ui-grid>
```

### `<ui-card title>`
Card con header opzionale. Senza `title` viene renderizzata senza header.

### `<ui-alert type="success|error|warning|info">`
Alert dismissibile via Alpine.js con transizione fade-out.

```html
<ui-alert type="success">Operazione completata</ui-alert>
<ui-alert type="error">Si è verificato un errore</ui-alert>
```

### `<ui-form method action>`
Wrapper form con `space-y-5`. Default: `method="POST"`.

### `<ui-input label name type value placeholder required disabled help>`
Campo input completo. `name`, `id` e `placeholder` vengono generati automaticamente da `label` se non specificati.

```html
<ui-input label="Email" type="email" required></ui-input>
<ui-input label="Note" help="Max 200 caratteri" disabled></ui-input>
<ui-input label="Nome" value="{{ @user.nome }}"></ui-input>
```

### `<ui-textarea label name rows required>`
Textarea con label. Default: `rows="3"`.

### `<ui-select label name required>`
Select con label. Le `<option>` passano direttamente come slot.

```html
<ui-select label="Ruolo" name="ruolo" required>
    <option value="">— Seleziona —</option>
    <option value="admin">Amministratore</option>
</ui-select>
```

### `<ui-button label type color>`
Bottone con varianti colore. Default: `type="submit"`, `color="blue"`.

Colori disponibili: `blue`, `green`, `red`, `gray`, `white`.

```html
<ui-button label="Salva" color="blue"></ui-button>
<ui-button label="Annulla" type="button" color="white"></ui-button>
<ui-button label="Elimina" color="red" type="button"></ui-button>
```

### `<ui-badge label color>`
Etichetta inline. Colori: `blue`, `green`, `red`, `yellow`, `gray`.

```html
<ui-badge label="Attivo"  color="green"></ui-badge>
<ui-badge label="Admin"   color="red"></ui-badge>
```

---

## Aggiungere un componente

1. Crea `components/ui-miocomponente.html` con la struttura HTML e i `{prop:*}` che ti servono
2. Aggiungi in `UI.php`:

```php
// In register():
$t->extend('ui-miocomponente', [self::class, 'mioComponente']);

// Handler (1 riga se non ha prop calcolati):
public static function mioComponente(array $node): string
{
    return self::compile('ui-miocomponente', $node);
}
```

3. Usalo nei template:

```html
<ui-miocomponente prop1="valore" prop2="{{ @var }}">
    <slot></slot>
</ui-miocomponente>
```

Non serve svuotare la cache durante lo sviluppo solo se `CACHE=false` in F3. In produzione, dopo aver modificato un componente, svuota la cartella `tmp/`.

---

## Cache F3

Con `CACHE=false` (sviluppo) i template vengono ricompilati ad ogni richiesta.  
Con `CACHE=true` (produzione) i template compilati vengono salvati in `tmp/`. Dopo aver modificato un file in `components/`, svuota `tmp/` per forzare la ricompilazione.

---

## Licenza

MIT
