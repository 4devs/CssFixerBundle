<?php

namespace FDevs\CssFixerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('f_devs_css_fixer');

        $rootNode
            ->children()
                ->arrayNode('include')
                    ->defaultValue([])
                    ->info("Bundles' names to process")
                    ->example('DemoBundle')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->arrayNode('exclude')
                    ->defaultValue([])
                    ->info("Bundles' names to ignore")
                    ->example('BadBundle')
                    ->prototype('scalar')
                    ->end()
                ->end()
                ->append($this->createFixerRulesSubtree())
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * @return ArrayNodeDefinition|NodeDefinition
     */
    private function createFixerRulesSubtree()
    {
        $treeBuilder = new TreeBuilder();
        $node = $treeBuilder->root('rules');

        // todo: add great validation
        // todo: test different values
        $node
            ->addDefaultsIfNotSet()
            ->children()
                ->scalarNode('block_indent')
                    ->defaultValue(4)
                    ->info('Set indent for code inside blocks, including media queries and nested rules')
                ->end()
                ->scalarNode('color_case')
                    ->defaultValue('lower')
                    ->info('Unify case of hexadecimal colors')
                    ->example('`lower` and `upper`')
                    ->validate()
                        ->ifNotInArray(['upper', 'lower'])
                            ->thenInvalid('element_case `%s`')
                    ->end()
                ->end()
                ->booleanNode('color_shorthand')
                    ->defaultFalse()
                    ->info('Whether to expand hexadecimal colors or use shorthands')
                    ->example('true - use shorthands, false - expand to 6 symbols')
                ->end()
                ->scalarNode('element_case')
                    ->defaultValue('lower')
                    ->info('Unify case of element selectors')
                    ->example('`lower` and `upper`')
                    ->validate()
                        ->ifNotInArray(['upper', 'lower'])
                            ->thenInvalid('element_case `%s`')
                    ->end()
                ->end()
                ->booleanNode('eof_newline')
                    ->defaultTrue()
                    ->info('Add/remove line break at EOF')
                    ->example('true - add line break, false - remove line break')
                ->end()
                ->arrayNode('exclude')
                    ->defaultValue(["**/**.min.css"])
                    ->info('List files that should be ignored while combing')
                    ->example('["node_modules/**"] — it uses Ant path patterns')
                    ->prototype('scalar')->end()
                ->end()
                ->booleanNode('leading_zero')
                    ->defaultTrue()
                    ->info('Add/remove leading zero in dimensions')
                    ->example('true - add, false - remove')
                ->end()
                ->scalarNode('quotes')
                    ->defaultValue('double')
                    ->info('Unify quotes style')
                    ->example('`double` or `single`')
                    ->validate()
                        ->ifNotInArray(['double', 'single'])
                            ->thenInvalid('Invalid quotes `%s`')
                    ->end()
                ->end()
                ->booleanNode('remove_empty_rulesets')
                    ->defaultTrue()
                    ->info('Remove all rulesets that contain nothing but spaces')
                ->end()
                ->arrayNode('sort_order')
                    ->info('Set sort order')
                    ->example('[[margin, padding], [font-weight, font-size]]')
                    ->prototype('array')
                        ->prototype('scalar')->end()
                    ->end()
                    ->beforeNormalization()
                        ->always(function ($groups) {
                            $groups = (array)$groups;
                            $commonGroup = [];
                            foreach ($groups as $id => $group) {
                                if (is_scalar($group)) {
                                    $commonGroup[] = $group;
                                    unset($groups[$id]);
                                }
                            }

                            if ($commonGroup) {
                                $groups[] = $commonGroup;
                            }

                            return array_values($groups);
                        })
                    ->end()
                ->end()
                ->scalarNode('sort_order_fallback')
                    ->defaultValue('abc')
                    ->info('Apply a special sort order for properties that are not specified in `sort_order` list')
                ->end()
                ->scalarNode('space_after_colon')
                    ->defaultValue(1)
                    ->info('Set space after : in declarations')
                ->end()
                ->scalarNode('space_after_combinator')
                    ->defaultValue(1)
                    ->info('Set space after combinator (for example, in selectors like p > a)')
                ->end()
                ->scalarNode('space_between_declarations')
                    ->defaultValue("\n")
                    ->info('Set space between declarations (i.e. color: tomato)')
                ->end()
                ->scalarNode('space_after_opening_brace')
                    ->defaultValue("\n")
                    ->info('Set space after `{`')
                ->end()
                ->scalarNode('space_after_selector_delimiter')
                    ->defaultValue(1)
                    ->info('Set space after selector delimiter')
                ->end()
                ->scalarNode('space_before_closing_brace')
                    ->defaultValue("\n")
                    ->info('Set space before `}`')
                ->end()
                ->scalarNode('space_before_colon')
                    ->defaultValue(0)
                    ->info('Set space before `:` in declarations')
                ->end()
                ->scalarNode('space_before_combinator')
                    ->defaultValue(1)
                    ->info('Set space before combinator (like p > a)')
                ->end()
                ->scalarNode('space_before_opening_brace')
                    ->defaultValue(1)
                    ->info('Set space before `{`')
                ->end()
                ->scalarNode('space_before_selector_delimiter')
                    ->defaultValue(0)
                    ->info('Set space before selector delimiter')
                ->end()
                ->booleanNode('strip_spaces')
                    ->defaultTrue()
                    ->info('Whether to trim trailing spaces')
                ->end()
                ->integerNode('tab_size')
                    ->defaultValue(4)
                    ->info('Set tab size (number of spaces to replace hard tabs)')
                ->end()
                ->booleanNode('unitless_zero')
                    ->defaultTrue()
                    ->info('Whether to remove units in zero-valued dimensions')
                ->end()
                ->booleanNode('always_semicolon')
                    ->defaultTrue()
                    ->info('Whether to add a semicolon after the last value/mixin')
                ->end()
                ->booleanNode('vendor_prefix_align')
                    ->defaultTrue()
                    ->info('Whether to align prefixes in properties and values')
                ->end()
            ->end()
        ;

        return $node;
    }
}
