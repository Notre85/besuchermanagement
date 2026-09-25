<?php

namespace App\Services;

interface TemplateRendererInterface
{
    public function render(array $templateVersion, array $data): array;
}
