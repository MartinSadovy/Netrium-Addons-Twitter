Dev 2.1 update:

everywhere:
$api = $this->context->getService('twitter.api');

bootstrap:

$configurator->onCompile[] = function(Nette\Configurator $configurator, Nette\DI\Compiler $compiler) {
    $compiler->addExtension('twitter', new Netrium\Addons\Twitter\TwitterExtension);
};