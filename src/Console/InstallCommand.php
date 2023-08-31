<?php

namespace Moawiaab\Role\Console;

use Illuminate\Support\Str;
use Illuminate\Console\Command;
use Illuminate\Contracts\Console\PromptsForMissingInput;
use RuntimeException;
use Symfony\Component\Process\Process;

class InstallCommand extends Command implements PromptsForMissingInput
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'moawiaab-role:install {stack : The development stack that should be installed (inertia,api)}
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
        if (!in_array($this->argument('stack'), ['inertia', 'api'])) {
            $this->components->error('Invalid stack. Supported stacks are [inertia] and [api].');

            return 1;
        }

        // Publish...
        // $this->callSilent('vendor:publish', ['--tag' => 'jetstream-config', '--force' => true]);

        // if (file_exists(resource_path('views/welcome.blade.php'))) {
        //     $this->replaceInFile('/home', '/dashboard', resource_path('views/welcome.blade.php'));
        //     $this->replaceInFile('Home', 'Dashboard', resource_path('views/welcome.blade.php'));
        // }

        $this->updateNodePackages(function ($packages) {
            return [
                "postcss-rtlcss" => "^4.0.7",
                "@quasar/vite-plugin" => "^1.4.1",
            ] + $packages;
        });

        $this->updateNodePackages(function ($packages) {
            return [
                "@quasar/extras" => "^1.16.5",
                "vue-i18n" => "^9.3.0-beta.25",
                "pinia" => "^2.1.6",
                "pinia-plugin-persistedstate" => "^3.2.0",
                "quasar" => "^2.12.5",
            ] + $packages;
        }, false);

        // set Middleware classes
        $this->installMiddlewareAfter('SubstituteBindings::class', '\Moawiaab\Role\Http\Middleware\AuthGates::class');

        // Install Stack...
        if ($this->argument('stack') === 'api') {
            if (!$this->installApiStack()) {
                return 1;
            }
        } elseif ($this->argument('stack') === 'inertia') {
            if (!$this->installInertiaStack()) {
                return 1;
            }
        }
    }

    /**
     * Install the Api stack into the application.
     *
     * @return bool
     */
    protected function installApiStack()
    {
        // Terms Of Service / Privacy Policy...
        copy(__DIR__ . '/../../stubs/resources/markdown/terms.md', resource_path('markdown/terms.md'));

        $this->line('');
        $this->components->info('Api scaffolding installed successfully.');

        return true;
    }


    /**
     * Install the Inertia stack into the application.
     *
     * @return bool
     */
    protected function installInertiaStack()
    {
        if (file_exists(base_path('postcss.config.js'))) {
            unlink(base_path('postcss.config.js'));
        }

        if (file_exists(base_path('vite.config.js'))) {
            unlink(base_path('vite.config.js'));
        }
        copy(__DIR__ . '/../../stubs/inertia/postcss.config.cjs', base_path('postcss.config.cjs'));
        copy(__DIR__ . '/../../stubs/inertia/vite.config.js', base_path('vite.config.js'));
        copy(__DIR__ . '/../../stubs/inertia/resources/sass/quasar-variables.sass', resource_path('sass/quasar-variables.sass'));
        
        copy(__DIR__.'/../../stubs/inertia/resources/js/app.js', resource_path('js/app.js'));

        if (file_exists(base_path('pnpm-lock.yaml'))) {
            $this->runCommands(['pnpm install', 'pnpm run build']);
        } elseif (file_exists(base_path('yarn.lock'))) {
            $this->runCommands(['yarn install', 'yarn run build']);
        } else {
            $this->runCommands(['npm install', 'npm run build']);
        }


        $this->line('');
        $this->components->info('Inertia scaffolding installed successfully.');

        return true;
    }

    /**
     * Returns the path to the correct test stubs.
     *
     * @return string
     */
    protected function getTestStubsPath()
    {
        return $this->option('pest') || $this->isUsingPest()
            ? __DIR__ . '/../../stubs/pest-tests'
            : __DIR__ . '/../../stubs/tests';
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

    /**
     * Determine whether the project is already using Pest.
     *
     * @return bool
     */
    protected function isUsingPest()
    {
        return class_exists(\Pest\TestSuite::class);
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
}
