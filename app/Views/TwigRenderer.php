<?php declare(strict_types=1);

namespace App\Renderers;

use App\Views\RendererInterface;
use Twig\Environment;

class TwigRenderer implements RendererInterface
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function render(string $template, array $data): string
    {
        return $this->twig->render($template, $data);
    }
}