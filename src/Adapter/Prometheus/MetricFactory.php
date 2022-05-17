<?php

namespace Metric\Adapter\Prometheus;

use GuzzleHttp\Client;
use Metric\Contract\CounterInterface;
use Metric\Contract\GaugeInterface;
use Metric\Contract\HistogramInterface;
use Metric\Contract\MetricFactoryInterface;
use Metric\Exception\InvalidArgumentException;
use Metric\Exception\RuntimeException;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;
use Swoole\Coroutine\Http\Server;

class MetricFactory implements MetricFactoryInterface
{
    /**
     * @var CollectorRegistry
     */
    private $registry;

    /**
     * @var array
     */
    private $config;

    /**
     * @var string
     */
    private $name;

    public function __construct(CollectorRegistry $registry, array $config)
    {
        $this->registry = $registry;
        $this->config = $config;
        $this->name = $this->config['default'] ?? 'prometheus';

    }

    public function makeCounter(string $name, ?array $labelNames = []): CounterInterface
    {
        return new Counter($this->registry, $this->getNamespace(), $name, 'count ' . str_replace('_', ' ', $name), $labelNames);
    }

    public function makeGauge(string $name, ?array $labelNames = []): GaugeInterface
    {
        return new Gauge($this->registry, $this->getNamespace(), $name, 'gauge ' . str_replace('_', ' ', $name), $labelNames);
    }

    public function makeHistogram(string $name, ?array $labelNames = [], $buckets = null): HistogramInterface
    {
        return new Histogram($this->registry, $this->getNamespace(), $name, 'measure ' . str_replace('_', ' ', $name), $labelNames, $buckets);
    }

    public function handle(int $returnHeader = 0): void
    {
        switch ($this->config['metric'][$this->name]['mode']) {
            case Constants::SCRAPE_MODE:
                $this->scrapeHandle($returnHeader);
                break;
            case Constants::PUSH_MODE:
                $this->pushHandle();
                break;
            default:
                throw new InvalidArgumentException('invalid prometheus mode');
        }
    }

    private function getNamespace(): string
    {
        $name = $this->config['metric'][$this->name]['namespace'];
        return str_replace('-', '_', $name);
    }

    /**
     * scape mode handle
     */
    protected function scrapeHandle($returnHeader)
    {
        $host = $this->config['metric'][$this->name]['scrape_host'];
        $port = $this->config['metric'][$this->name]['scrape_port'];
        $path = $this->config['metric'][$this->name]['scrape_path'];

        $render = new RenderTextFormat();
        $result = $render->render($this->registry->getMetricFamilySamples());
        if (extension_loaded('swoole') && $this->isCli()) {
            $server = new Server($host, (int)$port, false);
            $server->handle($path, function ($request, $response) use ($render) {
                $response->header('Content-Type', RenderTextFormat::MIME_TYPE);
                $response->end($result);
            });
        } else {
            if ($returnHeader) {
                header('Content-Type: ' . RenderTextFormat::MIME_TYPE);
                echo $result;
            } else {
                echo $result;
            }
        }
    }

    private function getUri(string $address, string $job)
    {
        if (strpos($address, 'http://') === false && strpos($address, 'https://') === false) {
            $address = 'http://' . $address;
        }
        return $address . '/metrics/job/' . $job;
    }

    private function doRequest(string $address, string $job, string $method)
    {
        $url = $this->getUri($address, $job);

        $client = new Client();
        $options = [
            'headers' => [
                'Context-Type' => RenderTextFormat::MIME_TYPE,
            ],
            'connect_timeout' => 10,
            'timeout' => 20,
        ];
        if ($method != 'delete') {
            $render = new RenderTextFormat();
            $options['body'] = $render->render($this->registry->getMetricFamilySamples());
        }
        $response = $client->request($method, $url, $options);
        $statusCode = $response->getStatusCode();
        if ($statusCode != 200 && $statusCode != 202) {
            $msg = 'unexpected status code ' . $statusCode . ' received from push gateway ' . $address . ': ' . $response->getBody();
            throw new RuntimeException($msg);
        }
    }

    protected function pushHandle()
    {
        while (true) {
            $interval = $this->config['metric'][$this->name]['push_interval'] ?? 5;
            $host = $this->config['metric'][$this->name]['push_host'];
            $port = $this->config['metric'][$this->name]['push_port'];

            $this->doRequest("{$host}:{$port}", $this->getNamespace(), 'put');
            sleep($interval);
        }
    }

    public function isCli()
    {
        return preg_match("/cli/i", php_sapi_name()) ? true : false;
    }
}