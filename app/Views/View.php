<?php

namespace App\Views;

use Twig\Environment;

class View
{
    private string $template;
    private array $data;

    public function __construct(string $template, array $data)
    {
        $this->template = $template;
        $this->data = $data;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getTemplate(): string
    {
        return $this->template;
    }

    public function render(Environment $twig): string
    {
        $template = $twig->load($this->template . '.twig');
        return $template->render($this->data);
    }
}