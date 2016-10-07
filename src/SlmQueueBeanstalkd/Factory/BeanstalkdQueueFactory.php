<?php

namespace SlmQueueBeanstalkd\Factory;

use Interop\Container\ContainerInterface;
use Interop\Container\Exception\ContainerException;
use SlmQueueBeanstalkd\Options\QueueOptions;
use SlmQueueBeanstalkd\Queue\BeanstalkdQueue;
use Zend\ServiceManager\Exception\ServiceNotCreatedException;
use Zend\ServiceManager\Exception\ServiceNotFoundException;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * BeanstalkdQueueFactory
 */
class BeanstalkdQueueFactory implements FactoryInterface
{
    /**
     * {@inheritDoc}
     */
    public function createService(ServiceLocatorInterface $serviceLocator, $name = '', $requestedName = '')
    {
        $parentLocator    = $serviceLocator->getServiceLocator();

        return $this($parentLocator, $requestedName);
    }

    /**
     * Returns custom beanstalkd options for specified queue
     * @param ServiceLocatorInterface $serviceLocator
     * @param string $queueName
     * @return QueueOptions
     */
    protected function getQueueOptions(ContainerInterface $serviceLocator, $queueName)
    {
        $config = $serviceLocator->get('Config');
        $queuesOptions = isset($config['slm_queue']['queues'])? $config['slm_queue']['queues'] : array();
        $queueOptions = isset($queuesOptions[$queueName])? $queuesOptions[$queueName] : array();

        return new QueueOptions($queueOptions);
    }

    /**
     * Create an object
     *
     * @param  ContainerInterface $container
     * @param  string $requestedName
     * @param  null|array $options
     * @return object
     * @throws ServiceNotFoundException if unable to resolve the service.
     * @throws ServiceNotCreatedException if an exception is raised when
     *     creating a service.
     * @throws ContainerException if any other error occurs
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $pheanstalk       = $container->get('SlmQueueBeanstalkd\Service\PheanstalkService');
        $jobPluginManager = $container->get('SlmQueue\Job\JobPluginManager');

        $queueOptions = $this->getQueueOptions($container, $requestedName);

        return new BeanstalkdQueue($pheanstalk, $requestedName, $jobPluginManager, $queueOptions);

    }
}
