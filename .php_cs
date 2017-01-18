<?php

$finder = Symfony\CS\Finder::create()
    ->in(__DIR__ . "/src")
;

return Symfony\CS\Config::create()
    ->level(Symfony\CS\FixerInterface::PSR2_LEVEL)
    ->setUsingCache(true)
    ->finder($finder)
    ;
