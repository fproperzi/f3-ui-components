<!DOCTYPE html>
<html lang="it">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars((string)($pageTitle),ENT_QUOTES,"UTF-8"); ?></title>
    <link rel="stylesheet" href="<?php echo htmlspecialchars((string)($BASE),ENT_QUOTES,"UTF-8"); ?>/css/app.css">
    <style>[x-cloak]{display:none!important}</style>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">


    <nav x-data="{ open: false }" class="bg-blue-700 text-white shadow-md">
    <div class="container mx-auto px-4">
        <div class="flex items-center justify-between h-16">
            <span class="text-xl font-bold tracking-tight"><?php echo htmlspecialchars((string)($pageTitle),ENT_QUOTES,"UTF-8"); ?></span>
            <div class="hidden md:flex items-center space-x-1">
                
        <a href="/"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (0): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Dashboard
</a>

        <a href="/utenti"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (1): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Utenti
</a>

        <a href="/report"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (0): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Report
</a>

    
            </div>
            <button @click="open = !open"
                    class="md:hidden p-2 rounded hover:bg-blue-800 focus:outline-none"
                    aria-label="Menu">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>
        <div x-show="open" x-cloak
             x-transition:enter="transition ease-out duration-150"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             class="md:hidden pb-4 flex flex-col space-y-1">
            
        <a href="/"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (0): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Dashboard
</a>

        <a href="/utenti"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (1): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Utenti
</a>

        <a href="/report"
   class="px-3 py-2 rounded-md text-sm font-medium transition-colors <?php if (0): ?>bg-blue-800 text-white<?php else: ?>text-blue-100 hover:text-white hover:bg-blue-800<?php endif; ?>">
    Report
</a>

    
        </div>
    </div>
</nav>


    <div class="container mx-auto px-4 py-6">
    

        <?php if ($saved): ?>
            
                <div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="flex items-start justify-between border-l-4 p-4 rounded-r-lg mb-4 bg-green-50 border-green-400 text-green-800">
    <div class="flex items-start gap-3">
        <span class="text-green-700 font-bold mt-0.5 select-none">&#10003;</span>
        <span class="text-sm">Utente creato correttamente</span>
    </div>
    <button @click="show = false"
            class="ml-4 text-lg font-bold opacity-40 hover:opacity-100 transition-opacity leading-none focus:outline-none"
            title="Chiudi">&times;</button>
</div>

            
        <?php endif; ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
    

            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <?php if (1): ?>
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Utente</h2>
    </div>
    <?php endif; ?>
    <div class="p-6">
        
                <form method="POST" <?php if (1): ?>action="/salva"<?php endif; ?> class="space-y-5">
    
                    <div class="flex flex-col gap-1">
    <?php if (1): ?>
    <label for="nome" class="text-sm font-medium text-gray-700">
        Nome<?php if (1): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <input type="text"
           id="nome"
           name="nome"
           value="<?php echo htmlspecialchars((string)($user['nome']),ENT_QUOTES,"UTF-8"); ?>"
           placeholder="Nome"
           required
           
           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  bg-white text-gray-900 placeholder-gray-400 transition-shadow
                  <?php if (0): ?>opacity-60 cursor-not-allowed bg-gray-50<?php endif; ?>">
    <?php if (0): ?>
    <p class="text-xs text-gray-500"></p>
    <?php endif; ?>
</div>


                    <div class="flex flex-col gap-1">
    <?php if (1): ?>
    <label for="email" class="text-sm font-medium text-gray-700">
        Email<?php if (1): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <input type="email"
           id="email"
           name="email"
           value="<?php echo htmlspecialchars((string)($user['email']),ENT_QUOTES,"UTF-8"); ?>"
           placeholder="Email"
           required
           
           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  bg-white text-gray-900 placeholder-gray-400 transition-shadow
                  <?php if (0): ?>opacity-60 cursor-not-allowed bg-gray-50<?php endif; ?>">
    <?php if (0): ?>
    <p class="text-xs text-gray-500"></p>
    <?php endif; ?>
</div>


                    <div class="flex flex-col gap-1">
    <?php if (1): ?>
    <label for="password" class="text-sm font-medium text-gray-700">
        Password<?php if (0): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <input type="password"
           id="password"
           name="password"
           value=""
           placeholder="Password"
           
           
           class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm
                  focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                  bg-white text-gray-900 placeholder-gray-400 transition-shadow
                  <?php if (0): ?>opacity-60 cursor-not-allowed bg-gray-50<?php endif; ?>">
    <?php if (1): ?>
    <p class="text-xs text-gray-500">Minimo 8 caratteri</p>
    <?php endif; ?>
</div>


                    <div class="flex flex-col gap-1">
    <?php if (1): ?>
    <label for="" class="text-sm font-medium text-gray-700">
        Ruolo<?php if (1): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <select id=""
            name="ruolo"
            required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm
                   focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                   bg-white text-gray-900 transition-shadow">
        
                        <option value="">— Seleziona —</option>
                        <option value="admin">Amministratore</option>
                        <option value="user">Utente</option>
                    
    </select>
</div>


                    <div class="flex flex-col gap-1">
    <?php if (1): ?>
    <label for="" class="text-sm font-medium text-gray-700">
        Note<?php if (0): ?><span class="text-red-500 ml-0.5">*</span><?php endif; ?>
    </label>
    <?php endif; ?>
    <textarea id=""
              name="note"
              rows="3"
              
              class="w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm text-sm
                     focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500
                     bg-white text-gray-900 resize-y transition-shadow"></textarea>
</div>


                    <div class="flex gap-3 pt-2">
                        <button type="submit"
        
        class="inline-flex items-center justify-center px-5 py-2.5 border rounded-lg text-sm
               font-medium shadow-sm transition-colors focus:outline-none focus:ring-2
               focus:ring-offset-2 bg-blue-600 hover:bg-blue-700 focus:ring-blue-500 text-white border-transparent
               <?php if (0): ?>opacity-50 cursor-not-allowed<?php endif; ?>">
    Salva
</button>

                        <button type="button"
        
        class="inline-flex items-center justify-center px-5 py-2.5 border rounded-lg text-sm
               font-medium shadow-sm transition-colors focus:outline-none focus:ring-2
               focus:ring-offset-2 bg-white hover:bg-gray-50 focus:ring-gray-300 text-gray-700 border-gray-300
               <?php if (0): ?>opacity-50 cursor-not-allowed<?php endif; ?>">
    Annulla
</button>

                    </div>
                
</form>

            
    </div>
</div>


            <div class="bg-white rounded-xl shadow-sm border border-gray-200 mb-6">
    <?php if (1): ?>
    <div class="px-6 py-4 border-b border-gray-200">
        <h2 class="text-lg font-semibold text-gray-800">Stato</h2>
    </div>
    <?php endif; ?>
    <div class="p-6">
        
                <div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="flex items-start justify-between border-l-4 p-4 rounded-r-lg mb-4 bg-blue-50 border-blue-400 text-blue-800">
    <div class="flex items-start gap-3">
        <span class="text-blue-700 font-bold mt-0.5 select-none">&#8505;</span>
        <span class="text-sm">Compila il form e premi Salva.</span>
    </div>
    <button @click="show = false"
            class="ml-4 text-lg font-bold opacity-40 hover:opacity-100 transition-opacity leading-none focus:outline-none"
            title="Chiudi">&times;</button>
</div>

                <div x-data="{ show: true }"
     x-show="show"
     x-cloak
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100"
     x-transition:leave-end="opacity-0"
     class="flex items-start justify-between border-l-4 p-4 rounded-r-lg mb-4 bg-yellow-50 border-yellow-400 text-yellow-800">
    <div class="flex items-start gap-3">
        <span class="text-yellow-700 font-bold mt-0.5 select-none">&#9888;</span>
        <span class="text-sm">Email deve essere univoca.</span>
    </div>
    <button @click="show = false"
            class="ml-4 text-lg font-bold opacity-40 hover:opacity-100 transition-opacity leading-none focus:outline-none"
            title="Chiudi">&times;</button>
</div>


                <p class="text-sm text-gray-600 mb-3">Badge disponibili:</p>
                <div class="flex flex-wrap gap-2">
                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
    Attivo
</span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
    Verificato
</span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
    Admin
</span>

                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
    Bozza
</span>

                </div>
            
    </div>
</div>


        
</div>


    
</div>



</body>
</html>
