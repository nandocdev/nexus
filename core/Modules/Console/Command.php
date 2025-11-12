<?php
namespace Nexus\Modules\Console;

abstract class Command
{
    public $signature;
    public $description;
    public $arguments = [];
    public $options = [];
    protected $optionDefinitions = [];

    public function __construct()
    {
        $this->configure();
        if (method_exists($this, 'configureOptions')) {
            $this->configureOptions();
        }
    }

    abstract protected function configure();

    abstract public function handle();

    public function getSignature()
    {
        return $this->signature;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function getArguments()
    {
        return $this->arguments;
    }

    public function getOptions()
    {
        return $this->options;
    }

    protected function argument($name)
    {
        return $this->arguments[$name] ?? null;
    }

    protected function option($name)
    {
        return $this->options[$name] ?? null;
    }

    protected function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    protected function info($message)
    {
        echo "\033[32m" . $message . "\033[0m" . PHP_EOL;
    }

    protected function error($message)
    {
        echo "\033[31m" . $message . "\033[0m" . PHP_EOL;
    }

    protected function warning($message)
    {
        echo "\033[33m" . $message . "\033[0m" . PHP_EOL;
    }

    protected function line($message)
    {
        echo $message . PHP_EOL;
    }

    protected function ask($question, $default = null)
    {
        echo $question;
        if ($default) {
            echo " [{$default}]";
        }
        echo ": ";
        $answer = trim(fgets(STDIN));
        return $answer ?: $default;
    }

    protected function confirm($question, $default = false)
    {
        $defaultText = $default ? 'Y/n' : 'y/N';
        $answer = $this->ask($question . " ({$defaultText})", $default ? 'y' : 'n');
        return strtolower($answer) === 'y' || ($default && $answer === '');
    }

    protected function addOption($name, $shortcut = null, $description = '', $default = null)
    {
        $this->optionDefinitions[$name] = [
            'shortcut' => $shortcut,
            'description' => $description,
            'default' => $default,
        ];
    }

    public function getOptionDefinitions()
    {
        return $this->optionDefinitions;
    }
}