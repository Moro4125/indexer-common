<?php

namespace Moro\Indexer\Common\Integration\Container7;

use Moro\Container7\Container;
use Moro\Container7\Parameters;
use Moro\Container7\Tags;
use Moro\Indexer\Common\Configuration\Adapter\MemoryAdapter as ConfigurationMemoryAdapter;
use Moro\Indexer\Common\Configuration\AdapterInterface as ConfigurationAdapterInterface;
use Moro\Indexer\Common\Configuration\ConfiguratorInterface;
use Moro\Indexer\Common\Configuration\Manager\ConfigurationManager;
use Moro\Indexer\Common\Configuration\ManagerInterface as ConfigurationManagerInterface;
use Moro\Indexer\Common\Configuration\Regulation\Decorator\ManagerDecorator as RegulationManagerDecorator;
use Moro\Indexer\Common\Configuration\Regulation\ManagerConfigurator as RegulationManagerConfigurator;
use Moro\Indexer\Common\Configuration\Regulation\TypeConfigurator as RegulationTypeConfigurator;
use Moro\Indexer\Common\Configuration\Regulation\UniversalInstructionConfigurator;
use Moro\Indexer\Common\Configuration\Source\Decorator\ManagerDecorator as SourceManagerDecorator;
use Moro\Indexer\Common\Configuration\Source\DoctrineDBALAdapterConfigurator;
use Moro\Indexer\Common\Configuration\Source\HttpApiAdapterConfigurator;
use Moro\Indexer\Common\Configuration\Source\ManagerConfigurator as SourceManagerConfigurator;
use Moro\Indexer\Common\Configuration\Source\TypeConfigurator as SourceTypeConfigurator;
use Moro\Indexer\Common\Configuration\Source\UniversalNormalizerConfigurator;
use Moro\Indexer\Common\Configuration\View\Decorator\ManagerDecorator as ViewManagerDecorator;
use Moro\Indexer\Common\Configuration\View\ManagerConfigurator as ViewManagerConfigurator;
use Moro\Indexer\Common\Configuration\View\TypeConfigurator as ViewTypeConfigurator;
use Moro\Indexer\Common\Configuration\View\UniversalKindConfigurator;
use Moro\Indexer\Common\Regulation\Factory\ContainerFactory as RegulationFactory;
use Moro\Indexer\Common\Regulation\Instruction\UniversalInstruction;
use Moro\Indexer\Common\Regulation\InstructionInterface;
use Moro\Indexer\Common\Regulation\ManagerInterface as RegulationManagerInterface;
use Moro\Indexer\Common\Regulation\Result\RegulationResult;
use Moro\Indexer\Common\Regulation\Type\RegulationType;
use Moro\Indexer\Common\Regulation\TypeInterface as RegulationTypeInterface;
use Moro\Indexer\Common\Source\Adapter\DoctrineDBALAdapter;
use Moro\Indexer\Common\Source\Adapter\HttpApiAdapter;
use Moro\Indexer\Common\Source\Entity\UniversalEntity;
use Moro\Indexer\Common\Source\Factory\ContainerFactory as SourceFactory;
use Moro\Indexer\Common\Source\ManagerInterface as SourceManagerInterface;
use Moro\Indexer\Common\Source\Normalizer\UniversalNormalizer;
use Moro\Indexer\Common\Source\Type\SourceType;
use Moro\Indexer\Common\Source\TypeInterface as SourceTypeInterface;
use Moro\Indexer\Common\Source\FactoryInterface as SourceFactoryInterface;
use Moro\Indexer\Common\View\Factory\ContainerFactory as ViewFactory;
use Moro\Indexer\Common\View\Kind\UniversalKind;
use Moro\Indexer\Common\View\ManagerInterface as ViewManagerInterface;
use Moro\Indexer\Common\View\TemplateInterface;
use Moro\Indexer\Common\View\Type\ViewType;
use Moro\Indexer\Common\View\TypeInterface as ViewTypeInterface;

/**
 * Class ConfigurationProvider
 * @package Moro\Indexer\Common\Integration\Container7
 */
class ConfigurationProvider
{
    const P_CONFIGURATION                = 'indexer.configuration';
    const P_CONFIGURATION_MANAGER_CLASS  = 'indexer/configuration/manager.class';
    const P_CONFIGURATION_CONFIGURATORS  = 'indexer/configuration/configurators';
    const P_SOURCE_NORMALIZER_CLASS      = 'indexer/source/normalizer.class';
    const P_SOURCE_ENTITY_CLASS          = 'indexer/source/entity.class';
    const P_REGULATION_INSTRUCTION_CLASS = 'indexer/regulation/instruction.class';
    const P_REGULATION_RESULT_CLASS      = 'indexer/regulation/result.class';
    const P_VIEW_KIND_CLASS              = 'indexer/view/kind.class';

    const TAG_CONFIGURATOR = 'indexer.configurator';

    public function parameters(Parameters $parameters)
    {
        $parameters->append([
            self::P_CONFIGURATION                => [],
            self::P_CONFIGURATION_MANAGER_CLASS  => ConfigurationManager::class,
            self::P_CONFIGURATION_CONFIGURATORS  => [
                SourceManagerInterface::class     => SourceManagerConfigurator::class,
                SourceTypeInterface::class        => SourceTypeConfigurator::class,
                UniversalNormalizer::class        => UniversalNormalizerConfigurator::class,
                HttpApiAdapter::class             => HttpApiAdapterConfigurator::class,
                DoctrineDBALAdapter::class        => DoctrineDBALAdapterConfigurator::class,
                RegulationManagerInterface::class => RegulationManagerConfigurator::class,
                RegulationTypeInterface::class    => RegulationTypeConfigurator::class,
                InstructionInterface::class       => UniversalInstructionConfigurator::class,
                ViewManagerInterface::class       => ViewManagerConfigurator::class,
                ViewTypeInterface::class          => ViewTypeConfigurator::class,
                UniversalKind::class              => UniversalKindConfigurator::class,
            ],
            self::P_SOURCE_NORMALIZER_CLASS      => UniversalNormalizer::class,
            self::P_SOURCE_ENTITY_CLASS          => UniversalEntity::class,
            self::P_REGULATION_INSTRUCTION_CLASS => UniversalInstruction::class,
            self::P_REGULATION_RESULT_CLASS      => RegulationResult::class,
            self::P_VIEW_KIND_CLASS              => UniversalKind::class,
        ]);

        $parameters->append([
            CommonProvider::P_TYPES                          => null,
            CommonProvider::P_SOURCE_MANAGER_LAZY_TARGET     => SourceManagerDecorator::class,
            CommonProvider::P_REGULATION_MANAGER_LAZY_TARGET => RegulationManagerDecorator::class,
            CommonProvider::P_VIEW_MANAGER_LAZY_TARGET       => ViewManagerDecorator::class,
        ]);
    }

    public function tags(Tags $tags)
    {
        $tags->add(Tags::RUNTIME, SourceTypeInterface::class);
        $tags->add(Tags::RUNTIME, RegulationTypeInterface::class);
        $tags->add(Tags::RUNTIME, ViewTypeInterface::class);

        $tags->add(SourceFactoryInterface::class, SourceFactory::class);
    }

    public function configurationAdapter(Parameters $parameters): ConfigurationAdapterInterface
    {
        return new ConfigurationMemoryAdapter($parameters->get(self::P_CONFIGURATION));
    }

    public function configurationManager(
        Container $container,
        Parameters $parameters,
        Tags $tags,
        ConfigurationAdapterInterface $adapter
    ): ConfigurationManagerInterface {
        $class = $parameters->get(self::P_CONFIGURATION_MANAGER_CLASS);

        /** @var ConfigurationManagerInterface $manager */
        $manager = $container->has($class) ? $container->get($class) : new $class;
        $manager->setAdapter($adapter);

        if ($container->hasCollection(self::TAG_CONFIGURATOR)) {
            $collection = $container->getCollection(ConfiguratorInterface::class)
                ->with(self::TAG_CONFIGURATOR);

            foreach ($collection as $key => $configurator) {
                $meta = $tags->metaByTagAndKey(self::TAG_CONFIGURATOR, $key);
                $manager->addConfigurator($meta['class'] ?? $meta['interface'], $configurator);
            }
        }

        foreach ($parameters->get(self::P_CONFIGURATION_CONFIGURATORS) as $interface => $class) {
            $configurator = $container->has($class) ? $container->get($class) : new $class();
            $manager->addConfigurator($interface, $configurator);
        }

        return $manager;
    }

    public function sourceType(...$arguments): SourceTypeInterface
    {
        $code = array_shift($arguments);

        $type = new SourceType();
        $type->setCode($code);

        return $type;
    }

    public function sourceFactory(Container $container, Parameters $parameters): SourceFactory
    {
        $factory = new SourceFactory($container);
        $factory->setNormalizerKey($parameters->get(self::P_SOURCE_NORMALIZER_CLASS));
        $factory->setEntityKey($parameters->get(self::P_SOURCE_ENTITY_CLASS));

        return $factory;
    }

    public function sourceManagerDecorator(
        ConfigurationManagerInterface $manager,
        SourceFactory $factory
    ): SourceManagerDecorator {
        return new SourceManagerDecorator($manager, $factory);
    }

    public function sourceManagerConfigurator(SourceFactory $factory): SourceManagerConfigurator
    {
        return new SourceManagerConfigurator($factory);
    }

    public function sourceTypeConfigurator(SourceFactory $factory): SourceTypeConfigurator
    {
        return new SourceTypeConfigurator($factory);
    }

    public function sourceUniversalNormalizer(...$arguments): UniversalNormalizer
    {
        unset($arguments);

        return new UniversalNormalizer();
    }

    public function regulationType(...$arguments): RegulationTypeInterface
    {
        $code = array_shift($arguments);

        $type = new RegulationType();
        $type->setCode($code);

        return $type;
    }

    public function regulationFactory(Container $container, Parameters $parameters): RegulationFactory
    {
        $factory = new RegulationFactory($container);
        $factory->setInstructionKey($parameters->get(self::P_REGULATION_INSTRUCTION_CLASS));
        $factory->setResultKey($parameters->get(self::P_REGULATION_RESULT_CLASS));

        return $factory;
    }

    public function regulationManagerDecorator(
        ConfigurationManagerInterface $manager,
        RegulationFactory $factory
    ): RegulationManagerDecorator {
        return new RegulationManagerDecorator($manager, $factory);
    }

    public function regulationManagerConfigurator(RegulationFactory $factory): RegulationManagerConfigurator
    {
        return new RegulationManagerConfigurator($factory);
    }

    public function regulationTypeConfigurator(RegulationFactory $factory): RegulationTypeConfigurator
    {
        return new RegulationTypeConfigurator($factory);
    }

    public function regulationUniversalInstruction(...$arguments): UniversalInstruction
    {
        unset($arguments);

        return new UniversalInstruction();
    }

    public function regulationResult(...$arguments): RegulationResult
    {
        unset($arguments);

        return new RegulationResult();
    }

    public function viewType(...$arguments): ViewTypeInterface
    {
        $code = array_shift($arguments);

        $type = new ViewType();
        $type->setCode($code);

        return $type;
    }

    public function viewFactory(Container $container, Parameters $parameters): ViewFactory
    {
        $factory = new ViewFactory($container);
        $factory->setKindKey($parameters->get(self::P_VIEW_KIND_CLASS));

        return $factory;
    }

    public function viewManagerDecorator(
        ConfigurationManagerInterface $manager,
        ViewFactory $factory
    ): ViewManagerDecorator {
        return new ViewManagerDecorator($manager, $factory);
    }

    public function viewManagerConfigurator(ViewFactory $factory): ViewManagerConfigurator
    {
        return new ViewManagerConfigurator($factory);
    }

    public function viewTypeConfigurator(ViewFactory $factory): ViewTypeConfigurator
    {
        return new ViewTypeConfigurator($factory);
    }

    public function viewUniversalKind(Container $container, ...$arguments): UniversalKind
    {
        unset($arguments);
        $render = $container->has(TemplateInterface::class) ? $container->get(TemplateInterface::class) : null;

        return new UniversalKind($render);
    }
}