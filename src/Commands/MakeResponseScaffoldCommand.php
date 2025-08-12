<?php

namespace TriQuang\LaravelResponseScaffold\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Log;

class MakeResponseScaffoldCommand extends Command
{
    private const AUTO_GEN_FLAG = '// AUTO-GEN-4-RESPONSE';
    private const PUBLISHED_STUB_PATH = 'stubs/vendor/triquang/laravel-response-scaffold';
    private const STUB_PATH = __DIR__ . '/../../stubs';

    protected $signature = 'make:response-scaffold';

    protected $description = 'Create standardized API responses, with exception and guest redirect handling.';

    protected $files;

    protected $appNamespace;

    protected $basePath;

    protected $appPath;

    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    public function handle()
    {
        try {
            $this->setupPaths();
            $this->createApiResponseFile();
            $this->createApiRouteFile();
            $this->injectApiRoute();
            $this->injectApiRedirectGuests();
            $this->injectExceptionHandler();

            $this->info('Response files generated successfully!');
            Log::info('Response files generated successfully!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Error generating Response files: ' . $e->getMessage());
            Log::error('Error generating Response files: ' . $e->getMessage());

            return self::FAILURE;
        }
    }

    protected function setupPaths()
    {
        $this->basePath = base_path();

        $this->appPath = base_path('app');

        $this->appNamespace = 'App';
    }

    protected function createApiResponseFile()
    {
        $path = $this->appPath . '/Support/Responses/ApiResponse.php';

        if ($this->files->exists($path)) {
            $this->warn('ApiResponse.php already exists, skipped.');
            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));

        $stub = $this->getStubContent('api-response.stub');
        $content = $this->replacePlaceholders($stub);

        $this->files->put($path, $content);

        $this->info('Created ApiResponse.php');
    }

    protected function createApiRouteFile()
    {
        $path = $this->basePath . '/routes/api.php';

        if ($this->files->exists($path)) {
            $this->warn('routes/api.php already exists, skipped.');
            return;
        }

        $this->files->ensureDirectoryExists(dirname($path));

        $stub = $this->getStubContent('route-api.stub');
        $content = $this->replacePlaceholders($stub);

        $this->files->put($path, $content);

        $this->info('Created routes/api.php');
    }

    protected function getStubContent(string $stubName): string
    {
        // Kiểm tra stub trong thư mục published (nếu có)
        $publishedPath = base_path(self::PUBLISHED_STUB_PATH . '/' . $stubName);
        if ($this->files->exists($publishedPath)) {
            return $this->files->get($publishedPath);
        }

        // Nếu không, lấy stub từ thư mục package
        $stubPath = self::STUB_PATH . '/' . $stubName;
        if (! $this->files->exists($stubPath)) {
            throw new \Exception("Stub file {$stubName} not found.");
        }

        return $this->files->get($stubPath);
    }

    protected function replacePlaceholders(string $content): string
    {
        return str_replace(
            ['{{ AUTO_GEN_FLAG }}'],
            [self::AUTO_GEN_FLAG],
            $content
        );
    }

    protected function insertAfterPhpOpenTag(string $content, array $useStatements, string $appFile): string
    {
        if (! $this->files->exists($appFile)) {
            $this->error("File {$appFile} not found.");
            return $content;
        }

        $changed = false;
        foreach ($useStatements as $useStatement) {
            if (! str_contains($content, $useStatement)) {
                $content = preg_replace(
                    '/\s*<\?php\s*(declare\(strict_types=1\);\s*)?/m',
                    "<?php\n$1\n{$useStatement}\n",
                    $content,
                    1
                );
                $changed = true;
            }
        }

        if ($changed) {
            $this->files->put($appFile, $content);
        }

        return $content;
    }

    protected function injectExceptionHandler()
    {
        $appFile = base_path('bootstrap/app.php');

        if (! $this->files->exists($appFile)) {
            $this->error('bootstrap/app.php not found.');
            return;
        }

        $content = $this->files->get($appFile);

        $useStatements = [
            'use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;',
            'use Illuminate\Validation\ValidationException;',
            'use Illuminate\Database\Eloquent\ModelNotFoundException;',
            'use Illuminate\Auth\AuthenticationException;',
            'use Illuminate\Auth\Access\AuthorizationException;',
            'use App\Support\Responses\ApiResponse;',
        ];

        $content = $this->insertAfterPhpOpenTag($content, $useStatements, $appFile);

        $marker = '->withExceptions(function (Exceptions $exceptions): void {';
        if (! str_contains($content, $marker)) {
            $this->warn("Marker '{$marker}' not found in bootstrap/app.php");
            return;
        }

        if (! str_contains($content, '// Handle exceptions globally for API requests')) {
            $exceptionCode = $this->getStubContent('exception-handler.stub');
            $content = str_replace(
                $marker,
                $marker . PHP_EOL . $exceptionCode,
                $content
            );

            $this->files->put($appFile, $content);
            $this->info('Injected global Exception Handler into bootstrap/app.php');
        } else {
            $this->warn('Global Exception Handler already exists, skipped.');
        }
    }

    protected function injectApiRedirectGuests()
    {
        $appFile = base_path('bootstrap/app.php');

        if (! $this->files->exists($appFile)) {
            $this->error('bootstrap/app.php not found.');
            return;
        }

        $content = $this->files->get($appFile);

        $useStatements = [
            'use Illuminate\Http\Request;',
        ];

        $content = $this->insertAfterPhpOpenTag($content, $useStatements, $appFile);

        $marker = '->withMiddleware(function (Middleware $middleware): void {';
        if (! str_contains($content, $marker)) {
            $this->warn("Marker '{$marker}' not found in bootstrap/app.php");
            return;
        }

        if (! str_contains($content, '// Prevent redirecting API unauthenticated users')) {
            $redirectCode = $this->getStubContent('api-redirect-guests.stub');
            $content = str_replace(
                $marker,
                $marker . PHP_EOL . $redirectCode,
                $content
            );

            $this->files->put($appFile, $content);
            $this->info('Injected API redirectGuestsTo fix into bootstrap/app.php');
        } else {
            $this->warn('API redirectGuestsTo fix already exists, skipped.');
        }
    }

    protected function injectApiRoute()
    {
        $appFile = base_path('bootstrap/app.php');

        if (! $this->files->exists($appFile)) {
            $this->error('bootstrap/app.php not found.');
            return;
        }

        $content = $this->files->get($appFile);

        $marker = '->withRouting(';
        if (! str_contains($content, $marker)) {
            $this->warn("Marker '{$marker}' not found in bootstrap/app.php");
            return;
        }

        $apiRoute = "api: __DIR__.'/../routes/api.php',";

        if (! str_contains($content, $apiRoute)) {
            $content = str_replace(
                $marker,
                $marker . PHP_EOL . '        '. $apiRoute,
                $content
            );

            $this->files->put($appFile, $content);
            $this->info('Injected API routing into bootstrap/app.php');
        } else {
            $this->warn('API routing already exists, skipped.');
        }
    }

}//