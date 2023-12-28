<?php return array (
  'app' => 
  array (
    'name' => 'Carlos',
    'env' => 'local',
    'debug' => true,
    'url' => 'http://pos5.test',
    'timezone' => 'America/Lima',
    'locale' => 'es',
    'fallback_locale' => 'en',
    'key' => 'base64:+SHAh7Jrj2y4zGQlIpX5mHcj+/16r+zPmBKU6kcRD58=',
    'cipher' => 'AES-256-CBC',
    'providers' => 
    array (
      0 => 'Illuminate\\Auth\\AuthServiceProvider',
      1 => 'Illuminate\\Broadcasting\\BroadcastServiceProvider',
      2 => 'Illuminate\\Bus\\BusServiceProvider',
      3 => 'Illuminate\\Cache\\CacheServiceProvider',
      4 => 'Illuminate\\Foundation\\Providers\\ConsoleSupportServiceProvider',
      5 => 'Illuminate\\Cookie\\CookieServiceProvider',
      6 => 'Illuminate\\Database\\DatabaseServiceProvider',
      7 => 'Illuminate\\Encryption\\EncryptionServiceProvider',
      8 => 'Illuminate\\Filesystem\\FilesystemServiceProvider',
      9 => 'Illuminate\\Foundation\\Providers\\FoundationServiceProvider',
      10 => 'Illuminate\\Hashing\\HashServiceProvider',
      11 => 'Illuminate\\Mail\\MailServiceProvider',
      12 => 'Illuminate\\Notifications\\NotificationServiceProvider',
      13 => 'Illuminate\\Pagination\\PaginationServiceProvider',
      14 => 'Illuminate\\Pipeline\\PipelineServiceProvider',
      15 => 'Illuminate\\Queue\\QueueServiceProvider',
      16 => 'Illuminate\\Redis\\RedisServiceProvider',
      17 => 'Illuminate\\Auth\\Passwords\\PasswordResetServiceProvider',
      18 => 'Illuminate\\Session\\SessionServiceProvider',
      19 => 'Illuminate\\Translation\\TranslationServiceProvider',
      20 => 'Illuminate\\Validation\\ValidationServiceProvider',
      21 => 'Illuminate\\View\\ViewServiceProvider',
      22 => 'Collective\\Html\\HtmlServiceProvider',
      23 => 'App\\Providers\\AppServiceProvider',
      24 => 'App\\Providers\\AuthServiceProvider',
      25 => 'App\\Providers\\EventServiceProvider',
      26 => 'App\\Providers\\RouteServiceProvider',
      27 => 'App\\Providers\\CacheServiceProvider',
      28 => 'App\\Providers\\ViewServiceProvider',
      29 => 'Maatwebsite\\Excel\\ExcelServiceProvider',
      30 => 'App\\Providers\\KardexServiceProvider',
      31 => 'App\\Providers\\AnulationServiceProvider',
      32 => 'App\\Providers\\InventoryKardexServiceProvider',
      33 => 'App\\Providers\\InventoryAnulationServiceProvider',
      34 => 'App\\Providers\\InventoryServiceProvider',
      35 => 'App\\Providers\\LockedEmissionProvider',
      36 => 'App\\Providers\\DocumentPaymentProvider',
      37 => 'Intervention\\Image\\ImageServiceProvider',
      38 => 'Rap2hpoutre\\LaravelLogViewer\\LaravelLogViewerServiceProvider',
      39 => 'App\\Providers\\CashServiceProvider',
      40 => 'Modules\\Finance\\Providers\\GlobalPaymentServiceProvider',
      41 => 'Modules\\Sale\\Providers\\SaleNotePaymentProvider',
      42 => 'Rap2hpoutre\\LaravelLogViewer\\LaravelLogViewerServiceProvider',
      43 => 'ZanySoft\\Zip\\ZipServiceProvider',
      44 => 'Barryvdh\\Debugbar\\ServiceProvider',
    ),
    'aliases' => 
    array (
      'App' => 'Illuminate\\Support\\Facades\\App',
      'Artisan' => 'Illuminate\\Support\\Facades\\Artisan',
      'Auth' => 'Illuminate\\Support\\Facades\\Auth',
      'Blade' => 'Illuminate\\Support\\Facades\\Blade',
      'Broadcast' => 'Illuminate\\Support\\Facades\\Broadcast',
      'Bus' => 'Illuminate\\Support\\Facades\\Bus',
      'Cache' => 'Illuminate\\Support\\Facades\\Cache',
      'Config' => 'Illuminate\\Support\\Facades\\Config',
      'Cookie' => 'Illuminate\\Support\\Facades\\Cookie',
      'Crypt' => 'Illuminate\\Support\\Facades\\Crypt',
      'DB' => 'Illuminate\\Support\\Facades\\DB',
      'Eloquent' => 'Illuminate\\Database\\Eloquent\\Model',
      'Event' => 'Illuminate\\Support\\Facades\\Event',
      'File' => 'Illuminate\\Support\\Facades\\File',
      'Gate' => 'Illuminate\\Support\\Facades\\Gate',
      'Hash' => 'Illuminate\\Support\\Facades\\Hash',
      'Lang' => 'Illuminate\\Support\\Facades\\Lang',
      'Log' => 'Illuminate\\Support\\Facades\\Log',
      'Mail' => 'Illuminate\\Support\\Facades\\Mail',
      'Notification' => 'Illuminate\\Support\\Facades\\Notification',
      'Password' => 'Illuminate\\Support\\Facades\\Password',
      'Queue' => 'Illuminate\\Support\\Facades\\Queue',
      'Redirect' => 'Illuminate\\Support\\Facades\\Redirect',
      'Redis' => 'Illuminate\\Support\\Facades\\Redis',
      'Request' => 'Illuminate\\Support\\Facades\\Request',
      'Response' => 'Illuminate\\Support\\Facades\\Response',
      'Route' => 'Illuminate\\Support\\Facades\\Route',
      'Schema' => 'Illuminate\\Support\\Facades\\Schema',
      'Session' => 'Illuminate\\Support\\Facades\\Session',
      'Storage' => 'Illuminate\\Support\\Facades\\Storage',
      'URL' => 'Illuminate\\Support\\Facades\\URL',
      'Validator' => 'Illuminate\\Support\\Facades\\Validator',
      'View' => 'Illuminate\\Support\\Facades\\View',
      'Excel' => 'Maatwebsite\\Excel\\Facades\\Excel',
      'Form' => 'Collective\\Html\\FormFacade',
      'Html' => 'Collective\\Html\\HtmlFacade',
      'Image' => 'Intervention\\Image\\Facades\\Image',
      'Zip' => 'ZanySoft\\Zip\\ZipFacade',
      'Markdown' => 'GrahamCampbell\\Markdown\\Facades\\Markdown',
    ),
  ),
  'auth' => 
  array (
    'defaults' => 
    array (
      'guard' => 'web',
      'passwords' => 'users',
    ),
    'guards' => 
    array (
      'web' => 
      array (
        'driver' => 'session',
        'provider' => 'users',
      ),
      'api' => 
      array (
        'driver' => 'token',
        'provider' => 'users',
      ),
      'admin' => 
      array (
        'driver' => 'session',
        'provider' => 'admins',
      ),
      'system_api' => 
      array (
        'driver' => 'token',
        'provider' => 'admins',
      ),
    ),
    'providers' => 
    array (
      'users' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\Tenant\\User',
      ),
      'admins' => 
      array (
        'driver' => 'eloquent',
        'model' => 'App\\Models\\System\\User',
      ),
    ),
    'passwords' => 
    array (
      'users' => 
      array (
        'provider' => 'users',
        'table' => 'password_resets',
        'expire' => 60,
        'connection' => 'tenant',
      ),
    ),
  ),
  'broadcasting' => 
  array (
    'default' => 'log',
    'connections' => 
    array (
      'pusher' => 
      array (
        'driver' => 'pusher',
        'key' => '',
        'secret' => '',
        'app_id' => '',
        'options' => 
        array (
          'cluster' => 'mt1',
          'encrypted' => true,
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
      ),
      'log' => 
      array (
        'driver' => 'log',
      ),
      'null' => 
      array (
        'driver' => 'null',
      ),
    ),
  ),
  'cache' => 
  array (
    'default' => 'file',
    'stores' => 
    array (
      'apc' => 
      array (
        'driver' => 'apc',
      ),
      'array' => 
      array (
        'driver' => 'array',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'cache',
        'connection' => NULL,
      ),
      'file' => 
      array (
        'driver' => 'file',
        'path' => 'C:\\laragon\\www\\pos5\\storage\\framework/cache/data',
      ),
      'memcached' => 
      array (
        'driver' => 'memcached',
        'persistent_id' => NULL,
        'sasl' => 
        array (
          0 => NULL,
          1 => NULL,
        ),
        'options' => 
        array (
        ),
        'servers' => 
        array (
          0 => 
          array (
            'host' => '127.0.0.1',
            'port' => 11211,
            'weight' => 100,
          ),
        ),
      ),
      'redis' => 
      array (
        'driver' => 'redis_tenancy',
        'connection' => 'default',
      ),
    ),
    'prefix' => 'carlos_cache',
  ),
  'configuration' => 
  array (
    'signature_note' => 'FACTURALO',
    'signature_uri' => 'signatureFACTURALOPERU',
    'api_service_url' => 'https://apiperu.dev',
    'api_service_token' => false,
    'sunat_alternate_server' => false,
    'app_url_base' => 'pos5.test',
  ),
  'cors' => 
  array (
    'supportsCredentials' => false,
    'allowedOrigins' => 
    array (
      0 => '*',
    ),
    'allowedOriginsPatterns' => 
    array (
    ),
    'allowedHeaders' => 
    array (
      0 => '*',
    ),
    'allowedMethods' => 
    array (
      0 => '*',
    ),
    'exposedHeaders' => 
    array (
    ),
    'maxAge' => 0,
  ),
  'database' => 
  array (
    'default' => 'system',
    'connections' => 
    array (
      'sqlite' => 
      array (
        'driver' => 'sqlite',
        'database' => 'pos5',
        'prefix' => '',
      ),
      'mysql' => 
      array (
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'pos5',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => NULL,
      ),
      'pgsql' => 
      array (
        'driver' => 'pgsql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'pos5',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
        'schema' => 'public',
        'sslmode' => 'prefer',
      ),
      'sqlsrv' => 
      array (
        'driver' => 'sqlsrv',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'pos5',
        'username' => 'root',
        'password' => '',
        'charset' => 'utf8',
        'prefix' => '',
      ),
      'system' => 
      array (
        'driver' => 'mysql',
        'host' => '127.0.0.1',
        'port' => '3306',
        'database' => 'pos5',
        'username' => 'root',
        'password' => '',
        'unix_socket' => '',
        'charset' => 'utf8mb4',
        'collation' => 'utf8mb4_unicode_ci',
        'prefix' => '',
        'strict' => true,
        'engine' => NULL,
        'options' => 
        array (
          1001 => true,
        ),
      ),
    ),
    'migrations' => 'migrations',
    'redis' => 
    array (
      'client' => 'predis',
      'default' => 
      array (
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => 0,
      ),
      'cache' => 
      array (
        'host' => '127.0.0.1',
        'password' => NULL,
        'port' => '6379',
        'database' => 1,
      ),
    ),
  ),
  'dompdf' => 
  array (
    'show_warnings' => false,
    'orientation' => 'portrait',
    'defines' => 
    array (
      'font_dir' => 'C:\\laragon\\www\\pos5\\storage\\fonts/',
      'font_cache' => 'C:\\laragon\\www\\pos5\\storage\\fonts/',
      'temp_dir' => 'C:\\Users\\andre\\AppData\\Local\\Temp',
      'chroot' => 'C:\\laragon\\www\\pos5',
      'enable_font_subsetting' => false,
      'pdf_backend' => 'CPDF',
      'default_media_type' => 'screen',
      'default_paper_size' => 'a4',
      'default_font' => 'serif',
      'dpi' => 96,
      'enable_php' => true,
      'enable_javascript' => true,
      'enable_remote' => true,
      'font_height_ratio' => 1.1,
      'enable_html5_parser' => false,
    ),
  ),
  'excel' => 
  array (
    'exports' => 
    array (
      'chunk_size' => 1000,
      'temp_path' => 'C:\\Users\\andre\\AppData\\Local\\Temp',
      'csv' => 
      array (
        'delimiter' => ';',
        'enclosure' => '',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
      ),
    ),
    'imports' => 
    array (
      'heading_row' => 
      array (
        'formatter' => 'slug',
      ),
      'csv' => 
      array (
        'delimiter' => ',',
        'enclosure' => '"',
        'line_ending' => '
',
        'use_bom' => false,
        'include_separator_line' => false,
        'excel_compatibility' => false,
      ),
    ),
    'extension_detector' => 
    array (
      'xlsx' => 'Xlsx',
      'xlsm' => 'Xlsx',
      'xltx' => 'Xlsx',
      'xltm' => 'Xlsx',
      'xls' => 'Xls',
      'xlt' => 'Xls',
      'ods' => 'Ods',
      'ots' => 'Ods',
      'slk' => 'Slk',
      'xml' => 'Xml',
      'gnumeric' => 'Gnumeric',
      'htm' => 'Html',
      'html' => 'Html',
      'csv' => 'Csv',
      'tsv' => 'Csv',
      'pdf' => 'Dompdf',
    ),
    'value_binder' => 
    array (
      'default' => 'Maatwebsite\\Excel\\DefaultValueBinder',
    ),
    'cache' => 
    array (
      'driver' => 'memory',
      'batch' => 
      array (
        'memory_limit' => 60000,
      ),
      'illuminate' => 
      array (
        'store' => NULL,
      ),
    ),
    'transactions' => 
    array (
      'handler' => 'db',
    ),
    'temporary_files' => 
    array (
      'local_path' => 'C:\\laragon\\www\\pos5\\storage\\framework/laravel-excel',
      'remote_disk' => NULL,
      'remote_prefix' => NULL,
      'force_resync_remote' => NULL,
    ),
  ),
  'extra' => 
  array (
    'number_items_in_search' => 250,
    'number_items_at_start' => 20,
    'extra_log' => false,
    'suscription_facturalo' => false,
    'apk_url' => 'https://facturaloperu.com/apk/app-debug.apk',
    'wiki_pharmacy' => 'http://pos5.test/docs/4.X/modulo-farmacia',
    'wiki_production' => 'https://gitlab.com/carlomagno83/facturadorpro4/-/wikis/App-Produccion',
    'AllowClientUseOwnApiperuToken' => false,
  ),
  'filesystems' => 
  array (
    'default' => 'local',
    'cloud' => 's3',
    'disks' => 
    array (
      'local' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\storage\\app',
      ),
      'public' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\storage\\app/public',
        'url' => 'http://pos5.test/storage',
        'visibility' => 'public',
      ),
      'core' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\app\\CoreFacturalo',
      ),
      's3' => 
      array (
        'driver' => 's3',
        'key' => NULL,
        'secret' => NULL,
        'region' => NULL,
        'bucket' => NULL,
        'url' => NULL,
      ),
      'ftp' => 
      array (
        'driver' => 'ftp',
        'host' => '',
        'port' => 21,
        'username' => '',
        'password' => '',
        'passive' => false,
      ),
      'tenancy-default' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\storage\\app/tenancy/tenants',
      ),
      'tenancy-webserver-apache2' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\storage\\app/tenancy/webserver/apache2',
      ),
      'tenancy-webserver-nginx' => 
      array (
        'driver' => 'local',
        'root' => 'C:\\laragon\\www\\pos5\\storage\\app/tenancy/webserver/nginx',
      ),
    ),
  ),
  'hashing' => 
  array (
    'driver' => 'bcrypt',
    'bcrypt' => 
    array (
      'rounds' => 10,
    ),
    'argon' => 
    array (
      'memory' => 1024,
      'threads' => 2,
      'time' => 2,
    ),
  ),
  'image' => 
  array (
    'driver' => 'gd',
  ),
  'larecipe' => 
  array (
    'docs' => 
    array (
      'route' => '/docs',
      'path' => '/resources/docs',
      'landing' => 'modulo-farmacia',
      'middleware' => 
      array (
        0 => 'web',
      ),
    ),
    'versions' => 
    array (
      'default' => '4.X',
      'published' => 
      array (
        0 => '4.X',
      ),
    ),
    'settings' => 
    array (
      'auth' => false,
      'ga_id' => '',
      'middleware' => 
      array (
        0 => 'web',
      ),
    ),
    'cache' => 
    array (
      'enabled' => false,
      'period' => 5,
    ),
    'search' => 
    array (
      'enabled' => true,
      'default' => 'internal',
      'engines' => 
      array (
        'internal' => 
        array (
          'index' => 
          array (
            0 => 'h2',
            1 => 'h3',
          ),
        ),
        'algolia' => 
        array (
          'key' => '',
          'index' => '',
        ),
      ),
    ),
    'ui' => 
    array (
      'code_theme' => 'dark',
      'fav' => '',
      'fa_v4_shims' => true,
      'show_side_bar' => true,
      'colors' => 
      array (
        'primary' => '#787AF6',
        'secondary' => '#2b9cf2',
      ),
      'theme_order' => NULL,
    ),
    'seo' => 
    array (
      'author' => '',
      'description' => '',
      'keywords' => '',
      'og' => 
      array (
        'title' => '',
        'type' => 'article',
        'url' => '',
        'image' => '',
        'description' => '',
      ),
    ),
    'forum' => 
    array (
      'enabled' => false,
      'default' => 'disqus',
      'services' => 
      array (
        'disqus' => 
        array (
          'site_name' => '',
        ),
      ),
    ),
    'packages' => 
    array (
      'path' => 'larecipe-components',
    ),
    'blade-parser' => 
    array (
      'regex' => 
      array (
        'code-blocks' => 
        array (
          'match' => '/\\<pre\\>(.|\\n)*?<\\/pre\\>/',
          'replacement' => '<code-block>',
        ),
      ),
    ),
  ),
  'location' => 
  array (
    'driver' => 'Stevebauman\\Location\\Drivers\\IpApi',
    'fallbacks' => 
    array (
      0 => 'Stevebauman\\Location\\Drivers\\IpInfo',
      1 => 'Stevebauman\\Location\\Drivers\\GeoPlugin',
      2 => 'Stevebauman\\Location\\Drivers\\MaxMind',
    ),
    'position' => 'Stevebauman\\Location\\Position',
    'maxmind' => 
    array (
      'web' => 
      array (
        'enabled' => false,
        'user_id' => '',
        'license_key' => '',
        'options' => 
        array (
          'host' => 'geoip.maxmind.com',
        ),
      ),
      'local' => 
      array (
        'type' => 'city',
        'path' => 'C:\\laragon\\www\\pos5\\database\\maxmind/GeoLite2-City.mmdb',
      ),
    ),
    'ip_api' => 
    array (
      'token' => NULL,
    ),
    'ipinfo' => 
    array (
      'token' => NULL,
    ),
    'ipdata' => 
    array (
      'token' => NULL,
    ),
    'kloudend' => 
    array (
      'token' => NULL,
    ),
    'testing' => 
    array (
      'enabled' => true,
      'ip' => '66.102.0.0',
    ),
  ),
  'logging' => 
  array (
    'default' => 'daily',
    'channels' => 
    array (
      'stack' => 
      array (
        'driver' => 'stack',
        'channels' => 
        array (
          0 => 'single',
        ),
      ),
      'single' => 
      array (
        'driver' => 'single',
        'path' => 'C:\\laragon\\www\\pos5\\storage\\logs/laravel.log',
        'level' => 'debug',
      ),
      'daily' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\laragon\\www\\pos5\\storage\\logs/laravel.log',
        'level' => 'debug',
        'days' => 7,
      ),
      'facturalo' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\laragon\\www\\pos5\\storage\\logs/facturalo.log',
        'level' => 'debug',
        'days' => 7,
      ),
      'emails' => 
      array (
        'driver' => 'daily',
        'path' => 'C:\\laragon\\www\\pos5\\storage\\logs/emails.log',
        'level' => 'debug',
        'days' => 7,
      ),
      'slack' => 
      array (
        'driver' => 'slack',
        'url' => NULL,
        'username' => 'Laravel Log',
        'emoji' => ':boom:',
        'level' => 'critical',
      ),
      'stderr' => 
      array (
        'driver' => 'monolog',
        'handler' => 'Monolog\\Handler\\StreamHandler',
        'with' => 
        array (
          'stream' => 'php://stderr',
        ),
      ),
      'syslog' => 
      array (
        'driver' => 'syslog',
        'level' => 'debug',
      ),
      'errorlog' => 
      array (
        'driver' => 'errorlog',
        'level' => 'debug',
      ),
    ),
  ),
  'mail' => 
  array (
    'driver' => 'smtp',
    'host' => 'smtp.gmail.com',
    'port' => '465',
    'from' => 
    array (
      'address' => 'hello@example.com',
      'name' => 'Example',
    ),
    'encryption' => 'ssl',
    'username' => 'soporte@joinec.net',
    'password' => 'oguictbcqghpsyha',
    'sendmail' => '/usr/sbin/sendmail -bs',
    'markdown' => 
    array (
      'theme' => 'default',
      'paths' => 
      array (
        0 => 'C:\\laragon\\www\\pos5\\resources\\views/vendor/mail',
      ),
    ),
  ),
  'markdown' => 
  array (
    'views' => true,
    'extensions' => 
    array (
    ),
    'renderer' => 
    array (
      'block_separator' => '
',
      'inner_separator' => '
',
      'soft_break' => '<br>',
    ),
    'enable_em' => true,
    'enable_strong' => true,
    'use_asterisk' => true,
    'use_underscore' => true,
    'html_input' => 'strip',
    'allow_unsafe_links' => true,
    'max_nesting_level' => INF,
  ),
  'modules' => 
  array (
    'namespace' => 'Modules',
    'stubs' => 
    array (
      'enabled' => false,
      'path' => 'C:\\laragon\\www\\pos5/vendor/nwidart/laravel-modules/src/Commands/stubs',
      'files' => 
      array (
        'routes/web' => 'Routes/web.php',
        'routes/api' => 'Routes/api.php',
        'views/index' => 'Resources/views/index.blade.php',
        'views/master' => 'Resources/views/layouts/master.blade.php',
        'scaffold/config' => 'Config/config.php',
        'composer' => 'composer.json',
        'assets/js/app' => 'Resources/assets/js/app.js',
        'assets/sass/app' => 'Resources/assets/sass/app.scss',
        'webpack' => 'webpack.mix.js',
        'package' => 'package.json',
      ),
      'replacements' => 
      array (
        'routes/web' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'routes/api' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'webpack' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'json' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'MODULE_NAMESPACE',
        ),
        'views/index' => 
        array (
          0 => 'LOWER_NAME',
        ),
        'views/master' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
        ),
        'scaffold/config' => 
        array (
          0 => 'STUDLY_NAME',
        ),
        'composer' => 
        array (
          0 => 'LOWER_NAME',
          1 => 'STUDLY_NAME',
          2 => 'VENDOR',
          3 => 'AUTHOR_NAME',
          4 => 'AUTHOR_EMAIL',
          5 => 'MODULE_NAMESPACE',
        ),
      ),
      'gitkeep' => false,
    ),
    'paths' => 
    array (
      'modules' => 'C:\\laragon\\www\\pos5\\modules',
      'assets' => 'C:\\laragon\\www\\pos5\\public\\modules',
      'migration' => 'C:\\laragon\\www\\pos5\\database/migrations',
      'generator' => 
      array (
        'config' => 
        array (
          'path' => 'Config',
          'generate' => true,
        ),
        'command' => 
        array (
          'path' => 'Console',
          'generate' => false,
        ),
        'migration' => 
        array (
          'path' => 'Database/Migrations',
          'generate' => false,
        ),
        'seeder' => 
        array (
          'path' => 'Database/Seeders',
          'generate' => false,
        ),
        'factory' => 
        array (
          'path' => 'Database/factories',
          'generate' => false,
        ),
        'model' => 
        array (
          'path' => 'Models',
          'generate' => true,
        ),
        'controller' => 
        array (
          'path' => 'Http/Controllers',
          'generate' => true,
        ),
        'filter' => 
        array (
          'path' => 'Http/Middleware',
          'generate' => false,
        ),
        'request' => 
        array (
          'path' => 'Http/Requests',
          'generate' => true,
        ),
        'resources' => 
        array (
          'path' => 'Http/Resources',
          'generate' => true,
        ),
        'provider' => 
        array (
          'path' => 'Providers',
          'generate' => true,
        ),
        'assets' => 
        array (
          'path' => 'Resources/assets',
          'generate' => true,
        ),
        'lang' => 
        array (
          'path' => 'Resources/lang',
          'generate' => true,
        ),
        'views' => 
        array (
          'path' => 'Resources/views',
          'generate' => true,
        ),
        'test' => 
        array (
          'path' => 'Tests',
          'generate' => false,
        ),
        'repository' => 
        array (
          'path' => 'Repositories',
          'generate' => false,
        ),
        'event' => 
        array (
          'path' => 'Events',
          'generate' => false,
        ),
        'listener' => 
        array (
          'path' => 'Listeners',
          'generate' => false,
        ),
        'policies' => 
        array (
          'path' => 'Policies',
          'generate' => false,
        ),
        'rules' => 
        array (
          'path' => 'Rules',
          'generate' => false,
        ),
        'jobs' => 
        array (
          'path' => 'Jobs',
          'generate' => false,
        ),
        'emails' => 
        array (
          'path' => 'Emails',
          'generate' => false,
        ),
        'notifications' => 
        array (
          'path' => 'Notifications',
          'generate' => false,
        ),
        'resource' => 
        array (
          'path' => 'Transformers',
          'generate' => false,
        ),
      ),
    ),
    'scan' => 
    array (
      'enabled' => false,
      'paths' => 
      array (
        0 => 'C:\\laragon\\www\\pos5\\vendor/*/*',
      ),
    ),
    'composer' => 
    array (
      'vendor' => 'nwidart',
      'author' => 
      array (
        'name' => 'Nicolas Widart',
        'email' => 'n.widart@gmail.com',
      ),
    ),
    'cache' => 
    array (
      'enabled' => false,
      'key' => 'laravel-modules',
      'lifetime' => 60,
    ),
    'register' => 
    array (
      'translations' => true,
      'files' => 'register',
    ),
  ),
  'queue' => 
  array (
    'default' => 'database',
    'connections' => 
    array (
      'sync' => 
      array (
        'driver' => 'sync',
      ),
      'database' => 
      array (
        'driver' => 'database',
        'table' => 'jobs',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'beanstalkd' => 
      array (
        'driver' => 'beanstalkd',
        'host' => 'localhost',
        'queue' => 'default',
        'retry_after' => 90,
      ),
      'sqs' => 
      array (
        'driver' => 'sqs',
        'key' => 'your-public-key',
        'secret' => 'your-secret-key',
        'prefix' => 'https://sqs.us-east-1.amazonaws.com/your-account-id',
        'queue' => 'your-queue-name',
        'region' => 'us-east-1',
      ),
      'redis' => 
      array (
        'driver' => 'redis',
        'connection' => 'default',
        'queue' => 'default',
        'retry_after' => 90,
        'block_for' => NULL,
      ),
    ),
    'failed' => 
    array (
      'database' => 'system',
      'table' => 'failed_jobs',
    ),
  ),
  'services' => 
  array (
    'mailgun' => 
    array (
      'domain' => NULL,
      'secret' => NULL,
    ),
    'ses' => 
    array (
      'key' => NULL,
      'secret' => NULL,
      'region' => 'us-east-1',
    ),
    'sparkpost' => 
    array (
      'secret' => NULL,
    ),
    'stripe' => 
    array (
      'model' => 'App\\User',
      'key' => NULL,
      'secret' => NULL,
    ),
  ),
  'session' => 
  array (
    'driver' => 'file',
    'lifetime' => '120',
    'expire_on_close' => false,
    'encrypt' => false,
    'files' => 'C:\\laragon\\www\\pos5\\storage\\framework/sessions',
    'connection' => NULL,
    'table' => 'sessions',
    'store' => NULL,
    'lottery' => 
    array (
      0 => 2,
      1 => 100,
    ),
    'cookie' => 'carlos_session',
    'path' => '/',
    'domain' => NULL,
    'secure' => false,
    'http_only' => true,
    'same_site' => NULL,
  ),
  'tables' => 
  array (
    'system' => 
    array (
      'state_types' => 
      array (
        '01' => 'Registrado',
        '03' => 'Enviado',
        '05' => 'Autorizado',
        '07' => 'Recibido',
        '09' => 'No Autorizado',
        11 => 'Anulado',
        13 => 'Anulando',
        15 => 'Anulando',
        30 => 'Devuelta',
        31 => 'Rechazado',
      ),
      'soap_sends' => 
      array (
        '01' => 'Sunat',
        '02' => 'Ose',
        '03' => 'Sri',
      ),
      'soap_types' => 
      array (
        '01' => 'Demo',
        '02' => 'Producción',
      ),
      'groups' => 
      array (
        '01' => 'F',
        '02' => 'B',
      ),
      'printing_formats' => 
      array (
        'a4' => 'A4',
        'ticket' => 'Ticket',
      ),
    ),
    'tenant' => 
    array (
      'document_types' => 
      array (
        '01' => 'Factura electrónica',
        '03' => 'Boleta electrónica',
        '07' => 'Nota de crédito electrónica',
        '08' => 'Nota de débito electrónica',
      ),
    ),
  ),
  'tenancy' => 
  array (
    'models' => 
    array (
      'hostname' => 'Hyn\\Tenancy\\Models\\Hostname',
      'website' => 'Hyn\\Tenancy\\Models\\Website',
    ),
    'middleware' => 
    array (
      0 => 'Hyn\\Tenancy\\Middleware\\EagerIdentification',
      1 => 'Hyn\\Tenancy\\Middleware\\HostnameActions',
    ),
    'website' => 
    array (
      'disable-random-id' => false,
      'random-id-generator' => 'Hyn\\Tenancy\\Generators\\Uuid\\ShaGenerator',
      'uuid-limit-length-to-32' => true,
      'disk' => NULL,
      'auto-create-tenant-directory' => true,
      'auto-rename-tenant-directory' => true,
      'auto-delete-tenant-directory' => false,
      'cache' => 10,
    ),
    'hostname' => 
    array (
      'default' => NULL,
      'auto-identification' => true,
      'early-identification' => true,
      'abort-without-identified-hostname' => false,
      'cache' => 10,
      'update-app-url' => false,
    ),
    'db' => 
    array (
      'default' => NULL,
      'system-connection-name' => 'system',
      'tenant-connection-name' => 'tenant',
      'tenant-division-mode' => 'database',
      'password-generator' => 'Hyn\\Tenancy\\Generators\\Database\\DefaultPasswordGenerator',
      'tenant-migrations-path' => 'C:\\laragon\\www\\pos5\\database\\migrations/tenant',
      'tenant-seed-class' => 'TenancyDatabaseSeeder',
      'auto-create-tenant-database' => true,
      'auto-create-tenant-database-user' => true,
      'auto-rename-tenant-database' => true,
      'auto-delete-tenant-database' => true,
      'auto-delete-tenant-database-user' => true,
      'force-tenant-connection-of-modelsforce-tenant-connection-of-models' => 
      array (
      ),
      'force-system-connection-of-models' => 
      array (
      ),
    ),
    'routes' => 
    array (
      'path' => 'C:\\laragon\\www\\pos5\\routes/tenants.php',
      'replace-global' => false,
    ),
    'folders' => 
    array (
      'config' => 
      array (
        'enabled' => true,
        'blacklist' => 
        array (
          0 => 'database',
          1 => 'tenancy',
          2 => 'webserver',
        ),
      ),
      'routes' => 
      array (
        'enabled' => true,
        'prefix' => NULL,
      ),
      'trans' => 
      array (
        'enabled' => true,
        'override-global' => true,
        'namespace' => 'tenant',
      ),
      'vendor' => 
      array (
        'enabled' => true,
      ),
      'media' => 
      array (
        'enabled' => true,
      ),
      'views' => 
      array (
        'enabled' => true,
        'namespace' => NULL,
        'override-global' => true,
      ),
    ),
  ),
  'tenant' => 
  array (
    'app_url_base' => 'pos5.test',
    'items_per_page' => '20',
    'items_per_page_simple_d_table' => '5',
    'items_per_page_simple_d_table_params' => 10,
    'password_change' => true,
    'prefix_database' => 'tenancy',
    'signature_note' => 'FACTURALO',
    'signature_uri' => '#FACTURALO',
    'force_https' => false,
    'document_type_03_filter' => false,
    'is_client' => false,
    'token_server' => 'YqlOsLAaajRfIChCshfFEcsVoMF2GmWOkZiy6YtapxZcf2yRoS',
    'url_server' => 'http://offline.2facturaloperuonline.com',
    'recreate_document' => false,
    'pdf_template' => 'default',
    'pdf_template_footer' => true,
    'pdf_name_regular' => false,
    'pdf_name_bold' => false,
    'change_to_registered_status' => false,
    'customer_multi_address' => false,
    'name_certificate_cron' => 'none',
    'import_documents' => false,
    'import_documents_second_format' => false,
    'enabled_template_ticket_80' => false,
    'enabled_template_ticket_70' => false,
    'enabled_discount_global' => false,
    'delete_document_type_03' => false,
    'select_first_document_type_03' => false,
    'admin_delete_client' => false,
    'soap_stream_context_ssl' => false,
    'apiperudev_validator_cpe' => true,
    'validator_cpe' => false,
    'auto_print' => false,
    'show_summary_status_type' => false,
    'document_import_excel' => false,
    'template_document_mail' => 'default',
    'delete_relation_note_to_invoice' => false,
    'igv_31556_start' => '2022-09-01',
    'igv_31556_end' => '2024-12-31',
    'igv_31556_percentage' => 0.1,
    'save_qrcode' => true,
  ),
  'view' => 
  array (
    'paths' => 
    array (
      0 => 'C:\\laragon\\www\\pos5\\resources\\views',
    ),
    'compiled' => 'C:\\laragon\\www\\pos5\\storage\\framework\\views',
  ),
  'webserver' => 
  array (
    'apache2' => 
    array (
      'enabled' => false,
      'ports' => 
      array (
        'http' => 80,
        'https' => 443,
      ),
      'generator' => 'Hyn\\Tenancy\\Generators\\Webserver\\Vhost\\ApacheGenerator',
      'view' => 'tenancy.generators::webserver.apache.vhost',
      'disk' => NULL,
      'paths' => 
      array (
        'vhost-files' => 
        array (
          0 => '/etc/apache2/sites-enabled/',
        ),
        'actions' => 
        array (
          'exists' => '/etc/init.d/apache2',
          'test-config' => 'apache2ctl -t',
          'reload' => 'apache2ctl graceful',
        ),
      ),
    ),
    'nginx' => 
    array (
      'enabled' => false,
      'php-sock' => 'unix:/var/run/php/php7.1-fpm.sock',
      'ports' => 
      array (
        'http' => 80,
        'https' => 443,
      ),
      'generator' => 'Hyn\\Tenancy\\Generators\\Webserver\\Vhost\\NginxGenerator',
      'view' => 'tenancy.generators::webserver.nginx.vhost',
      'disk' => NULL,
      'paths' => 
      array (
        'vhost-files' => 
        array (
          0 => '/etc/nginx/sites-enabled/',
        ),
        'actions' => 
        array (
          'exists' => '/etc/init.d/nginx',
          'test-config' => '/etc/init.d/nginx configtest',
          'reload' => '/etc/init.d/nginx reload',
        ),
      ),
    ),
  ),
  'debugbar' => 
  array (
    'enabled' => NULL,
    'except' => 
    array (
      0 => 'telescope*',
    ),
    'storage' => 
    array (
      'enabled' => true,
      'driver' => 'file',
      'path' => 'C:\\laragon\\www\\pos5\\storage\\debugbar',
      'connection' => NULL,
      'provider' => '',
    ),
    'include_vendors' => true,
    'capture_ajax' => true,
    'add_ajax_timing' => false,
    'error_handler' => false,
    'clockwork' => false,
    'collectors' => 
    array (
      'phpinfo' => true,
      'messages' => true,
      'time' => true,
      'memory' => true,
      'exceptions' => true,
      'log' => true,
      'db' => true,
      'views' => true,
      'route' => true,
      'auth' => false,
      'gate' => true,
      'session' => true,
      'symfony_request' => true,
      'mail' => true,
      'laravel' => false,
      'events' => false,
      'default_request' => false,
      'logs' => false,
      'files' => false,
      'config' => false,
      'cache' => false,
      'models' => false,
    ),
    'options' => 
    array (
      'auth' => 
      array (
        'show_name' => true,
      ),
      'db' => 
      array (
        'with_params' => true,
        'backtrace' => true,
        'timeline' => false,
        'explain' => 
        array (
          'enabled' => false,
          'types' => 
          array (
            0 => 'SELECT',
          ),
        ),
        'hints' => true,
      ),
      'mail' => 
      array (
        'full_log' => false,
      ),
      'views' => 
      array (
        'data' => false,
      ),
      'route' => 
      array (
        'label' => true,
      ),
      'logs' => 
      array (
        'file' => NULL,
      ),
      'cache' => 
      array (
        'values' => true,
      ),
    ),
    'inject' => true,
    'route_prefix' => '_debugbar',
    'route_domain' => NULL,
  ),
  'debug-server' => 
  array (
    'host' => 'tcp://127.0.0.1:9912',
  ),
  'trustedproxy' => 
  array (
    'proxies' => NULL,
    'headers' => 30,
  ),
  'account' => 
  array (
    'name' => 'Account',
  ),
  'mobileapp' => 
  array (
    'name' => 'MobileApp',
  ),
  'suscription' => 
  array (
    'name' => 'Suscription',
  ),
  'sale' => 
  array (
    'name' => 'Sale',
  ),
  'restaurant' => 
  array (
    'name' => 'Restaurant',
  ),
  'report' => 
  array (
    'name' => 'Report',
  ),
  'purchase' => 
  array (
    'name' => 'Purchase',
  ),
  'production' => 
  array (
    'name' => 'Production',
  ),
  'pos' => 
  array (
    'name' => 'Pos',
  ),
  'payment' => 
  array (
    'name' => 'Payment',
  ),
  'padron' => 
  array (
    'name' => 'Padron',
  ),
  'order' => 
  array (
    'name' => 'Order',
  ),
  'offline' => 
  array (
    'name' => 'Offline',
  ),
  'mercadopago' => 
  array (
    'name' => 'MercadoPago',
  ),
  'levelaccess' => 
  array (
    'name' => 'LevelAccess',
  ),
  'item' => 
  array (
    'name' => 'Item',
  ),
  'inventory' => 
  array (
    'name' => 'Inventory',
  ),
  'hotel' => 
  array (
    'name' => 'Hotel',
  ),
  'full_suscription' => 
  array (
    'name' => 'Full Suscription',
  ),
  'finance' => 
  array (
    'name' => 'Finance',
  ),
  'expense' => 
  array (
    'name' => 'Expense',
  ),
  'ecommerce' => 
  array (
    'name' => 'Ecommerce',
  ),
  'documentaryprocedure' => 
  array (
    'name' => 'DocumentaryProcedure',
  ),
  'document' => 
  array (
    'name' => 'Document',
  ),
  'digemid' => 
  array (
    'name' => 'Digemid',
  ),
  'dashboard' => 
  array (
    'name' => 'Dashboard',
  ),
  'businessturn' => 
  array (
    'name' => 'BusinessTurn',
  ),
  'whatsappapi' => 
  array (
    'name' => 'WhatsAppApi',
    'whatsapp_cloud_api_version' => 'v14.0',
  ),
  'tinker' => 
  array (
    'commands' => 
    array (
    ),
    'dont_alias' => 
    array (
      0 => 'App\\Nova',
    ),
  ),
);
