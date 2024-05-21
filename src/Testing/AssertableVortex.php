<?php

namespace WeStacks\Laravel\Vortex\Testing;

use Illuminate\Testing\Assert;
use Illuminate\Testing\Fluent\AssertableJson;
use Illuminate\Testing\TestResponse;
use Illuminate\View\FileViewFinder;
use PHPUnit\Framework\AssertionFailedError;

class AssertableVortex extends AssertableJson
{
    private string $component;
    private string $url;
    private ?string $version;

    public static function fromResponse(TestResponse $response): self
    {
        try {
            $response->assertViewHas('page');
            $page = $response->viewData('page');
            json_encode($page, JSON_THROW_ON_ERROR);

            Assert::assertIsArray($page);
            Assert::assertArrayHasKey('component', $page);
            Assert::assertArrayHasKey('props', $page);
            Assert::assertArrayHasKey('url', $page);
            Assert::assertArrayHasKey('version', $page);
        } catch (\JsonException|AssertionFailedError  $e) {
            Assert::fail("Not a valid Vortex response.");
        }

        $instance = static::fromArray($page['props']);
        $instance->component = $page['component'];
        $instance->url = $page['url'];
        $instance->version = $page['version'];

        return $instance;
    }

    public function component(string $value = null, bool $exist = false): self
    {
        Assert::assertSame($value, $this->component, "Component value '{$this->component}' is not '{$value}'.");

        if ($exist) {
            $finder = new FileViewFinder(
                app('files'),
                config('vortex.testing.paths', []),
                config('vortex.testing.extensions', [])
            );

            rescue(
                fn () => $finder->find($value),
                fn () => Assert::fail("Component '{$value}' does not exist."),
                report: false,
            );
        }

        return $this;
    }

    public function url(string $value): self
    {
        Assert::assertSame($value, $this->url, "Unexpected URL value '{$value}'. Expected '{$this->url}'.");

        return $this;
    }

    public function version(string $value): self
    {
        Assert::assertSame($value, $this->version, "Unexpected version value '{$value}'. Expected '{$this->version}'.");

        return $this;
    }

    public function toArray()
    {
        return [
            'component' => $this->component,
            'props' => $this->prop(),
            'url' => $this->url,
            'version' => $this->version,
        ];
    }
}
