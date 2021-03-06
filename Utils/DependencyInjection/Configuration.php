<?php declare(strict_types = 1);

namespace Vairogs\Component\Utils\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Vairogs\Component\Auth\DependencyInjection\AuthDependency;
use Vairogs\Component\Cache\DependencyInjection\CacheDependency;
use Vairogs\Component\Sitemap\DependencyInjection\SitemapDependency;
use Vairogs\Component\Translation\DependencyInjection\TranslationDependency;
use Vairogs\Component\Utils\Vairogs;
use function class_exists;

class Configuration implements ConfigurationInterface
{
    /**
     * @return TreeBuilder
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder(Vairogs::VAIROGS);
        $rootNode = $treeBuilder->getRootNode();

        $this->appendCacheNode($rootNode);
        $this->appendAuthNode($rootNode);
        $this->appendSitemapNode($rootNode);
        $this->appendTranslationNode($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $arrayNodeDefinition
     */
    private function appendCacheNode(ArrayNodeDefinition $arrayNodeDefinition): void
    {
        if (class_exists(CacheDependency::class)) {
            (new CacheDependency())->getConfiguration($arrayNodeDefinition);
        }
    }

    /**
     * @param ArrayNodeDefinition $arrayNodeDefinition
     */
    private function appendAuthNode(ArrayNodeDefinition $arrayNodeDefinition): void
    {
        if (class_exists(AuthDependency::class)) {
            (new AuthDependency())->getConfiguration($arrayNodeDefinition);
        }
    }

    /**
     * @param ArrayNodeDefinition $arrayNodeDefinition
     */
    private function appendSitemapNode(ArrayNodeDefinition $arrayNodeDefinition): void
    {
        if (class_exists(SitemapDependency::class)) {
            (new SitemapDependency())->getConfiguration($arrayNodeDefinition);
        }
    }

    /**
     * @param ArrayNodeDefinition $arrayNodeDefinition
     */
    private function appendTranslationNode(ArrayNodeDefinition $arrayNodeDefinition): void
    {
        if (class_exists(TranslationDependency::class)) {
            (new TranslationDependency())->getConfiguration($arrayNodeDefinition);
        }
    }
}
