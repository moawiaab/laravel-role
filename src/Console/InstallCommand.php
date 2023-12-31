<?php

namespace Moawiaab\Role\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use RuntimeException;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Process\Process;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

use function Laravel\Prompts\multiselect;
use function Laravel\Prompts\select;

class InstallCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moawiaab-role:install {stack : The development stack that should be installed (quasar,tailwind)}
                                                  {--dark : Indicate that dark mode support should be installed}
                                                  {--lang : Make Arabic the default language}
                                                  {--pinia : Indicates if pinia support should be installed}
                                                  {--persistedstate : Indicates if pinia-plugin-persistedstate support should be installed}
                                                  {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the moawiaab-role components and resources';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        if (!in_array($this->argument('stack'), ['quasar', 'tailwind'])) {
            $this->components->error('Invalid stack. Supported stacks are [quasar] and [tailwind].');

            return 1;
        }

        // Publish...
        // $this->callSilent('vendor:publish', ['--tag' => 'jetstream-config', '--force' => true]);

        // if (file_exists(resource_path('views/welcome.blade.php'))) {
        //     $this->replaceInFile('/home', '/dashboard', resource_path('views/welcome.blade.php'));
        //     $this->replaceInFile('Home', 'Dashboard', resource_path('views/welcome.blade.php'));
        // }

        // set Middleware classes
        $this->installMiddlewareAfter('SubstituteBindings::class', '\Moawiaab\Role\Http\Middleware\AuthGates::class');


        if ($this->option('pinia')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    "pinia" => "^2.1.6"
                ] + $packages;
            });
        }

        if ($this->option('persistedstate')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    "pinia" => "^2.1.6",
                    "pinia-plugin-persistedstate" => "^3.2.0"
                ] + $packages;
            });
        }

        // Install Stack...
        if ($this->argument('stack') === 'tailwind') {
            if (!$this->installTailwindStack()) {
                return 1;
            }
        } elseif ($this->argument('stack') === 'quasar') {
            if (!$this->installQuasarStack()) {
                return 1;
            }
        }
    }

    /**
     * Install the Api stack into the application.
     *
     * @return bool
     */
    protected function installTailwindStack()
    {
        // Terms Of Service / Privacy Policy...

        if (!$this->option('dark')) {
            $this->removeDarkClasses((new Finder)
                    ->in(resource_path('js'))
                    ->name('*.vue')
                    ->notPath('Pages/Welcome.vue')
            );
        }

        $this->line('');
        $this->components->info('tailwindcss theme installed successfully.');

        return true;
    }


    /**
     * Install the Inertia stack into the application.
     *
     * @return bool
     */
    protected function installQuasarStack()
    {
        if (file_exists(base_path('postcss.config.js'))) {
            unlink(base_path('postcss.config.js'));
        }

        if (file_exists(base_path('vite.config.js'))) {
            unlink(base_path('vite.config.js'));
        }
        copy(__DIR__ . '/../../stubs/inertia/postcss.config.cjs', base_path('postcss.config.cjs'));
        copy(__DIR__ . '/../../stubs/inertia/vite.config.js', base_path('vite.config.js'));
        (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/inertia/resources/sass/quasar-variables.sass', resource_path('sass/quasar-variables.sass'));
        (new Filesystem)->copyDirectory(__DIR__.'/../../stubs/inertia/resources/js', resource_path('js'));
        // copy(__DIR__ . '/../../stubs/inertia/resources/js/app.js', resource_path('js/app.js'));

        $this->updateNodePackages(function ($packages) {
            return [
                "@quasar/vite-plugin" => "^1.4.1",
            ] + $packages;
        });

        $this->updateNodePackages(function ($packages) {
            return [
                "@quasar/extras" => "^1.16.6",
                "quasar" => "^2.12.5",
            ] + $packages;
        }, false);

        if ($this->option('lang')) {
            $this->updateNodePackages(function ($packages) {
                return [
                    "postcss-rtlcss" => "^4.0.7",
                    "vue-i18n" => "^9.3.0-beta.25",
                ] + $packages;
            });

            $this->replaceInFile('locale: "en-US",', 'locale: "ar",', resource_path('js/app.js'));
            $this->replaceInFile('lang: quasarLangEn,', 'lang: quasarLangAr,', resource_path('js/app.js'));
            $this->replaceInFile('rtl: false', 'rtl: true', resource_path('js/app.js'));

        }



        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }


        $this->line('');
        $this->components->info('quasar theme installed successfully.');

        return true;
    }

    /**
     * Update the "package.json" file.
     *
     * @param  callable  $callback
     * @param  bool  $dev
     * @return void
     */
    protected static function updateNodePackages(callable $callback, $dev = true)
    {
        if (!file_exists(base_path('package.json'))) {
            return;
        }

        $configurationKey = $dev ? 'devDependencies' : 'dependencies';

        $packages = json_decode(file_get_contents(base_path('package.json')), true);

        $packages[$configurationKey] = $callback(
            array_key_exists($configurationKey, $packages) ? $packages[$configurationKey] : [],
            $configurationKey
        );

        ksort($packages[$configurationKey]);

        file_put_contents(
            base_path('package.json'),
            json_encode($packages, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) . PHP_EOL
        );
    }

    /**
     * Replace a given string within a given file.
     *
     * @param  string  $search
     * @param  string  $replace
     * @param  string  $path
     * @return void
     */
    protected function replaceInFile($search, $replace, $path)
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }

    /**
     * Run the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    ' . $line);
        });
    }

    protected function installMiddlewareAfter($after, $name, $group = 'web')
    {
        $httpKernel = file_get_contents(app_path('Http/Kernel.php'));

        $middlewareGroups = Str::before(Str::after($httpKernel, '$middlewareGroups = ['), '];');
        $middlewareGroup = Str::before(Str::after($middlewareGroups, "'$group' => ["), '],');

        if (!Str::contains($middlewareGroup, $name)) {
            $modifiedMiddlewareGroup = str_replace(
                $after . ',',
                $after . ',' . PHP_EOL . '            ' . $name . ',',
                $middlewareGroup,
            );

            file_put_contents(app_path('Http/Kernel.php'), str_replace(
                $middlewareGroups,
                str_replace($middlewareGroup, $modifiedMiddlewareGroup, $middlewareGroups),
                $httpKernel
            ));
        }
    }


    protected function promptForMissingArgumentsUsing()
    {
        return [
            'stack' => fn () => select(
                label: 'Which moawiaab-role theme would you like to install?',
                options: [
                    'quasar' => 'quasar',
                    'tailwind' => 'tailwind',
                ]
            ),
        ];
    }

    /**
     * Interact further with the user if they were prompted for missing arguments.
     *
     * @param  \Symfony\Component\Console\Input\InputInterface  $input
     * @param  \Symfony\Component\Console\Output\OutputInterface  $output
     * @return void
     */
    protected function afterPromptingForMissingArguments(InputInterface $input, OutputInterface $output)
    {
        collect(multiselect(
            label: 'Would you like any optional features?',
            options: collect([
                'pinia' => 'pinia store',
                'persistedstate' => 'pinia plugin persistedstate',
            ])->when(
                $input->getArgument('stack') === 'tailwind',
                fn ($options) => $options->put('dark', 'Dark mode')
            )->when(
                $input->getArgument('stack') === 'quasar',
                fn ($options) => $options->put('lang', 'Select Arabic language')
            )->sort()->sort()->all(),
        ))->each(fn ($option) => $input->setOption($option, true));

        // $input->setOption('pest', select(
        //     label: 'Which testing framework do you prefer?',
        //     options: ['PHPUnit', 'Pest'],
        //     default: 'default',
        // ) === 'Pest');
    }
}
