<?php

namespace FmTod\SmsCommunications\Concerns;

use Mockery;
use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\MockInterface;

trait MockableService
{
    public static function make(...$args): static
    {
        throw_if(empty($args), \Exception::class, 'No configuration provided');

        $configuration = array_values($args)[0];

        throw_unless($configuration, \Exception::class, 'No configuration provided');

        return app(self::class, ['configuration' => $configuration]);
    }

    public static function mock(): MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        $mock = Mockery::mock(static::class);
        $mock->shouldAllowMockingProtectedMethods();

        return static::setFakeResolvedInstance($mock);
    }

    public static function spy(): MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        return static::setFakeResolvedInstance(Mockery::spy(static::class));
    }

    public static function partialMock(): MockInterface
    {
        return static::mock()->makePartial();
    }

    public static function shouldSendMessage(): Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
    {
        return static::mock()->allows('sendMessage');
    }

    public static function shouldNotSendMessage(): HigherOrderMessage|Expectation|ExpectationInterface
    {
        return static::mock()
            ->allows('sendMessage')
            ->never();
    }

    public static function allowToSendMessage(): HigherOrderMessage|Expectation|MockInterface|ExpectationInterface
    {
        return static::spy()->allows('sendMessage');
    }

    public static function isFake(): bool
    {
        return app()->isShared(static::getFakeResolvedInstanceKey());
    }

    public static function clearFake(): void
    {
        app()->forgetInstance(static::getFakeResolvedInstanceKey());
    }

    protected static function setFakeResolvedInstance(MockInterface $fake): MockInterface
    {
        $instance = app()->instance(static::getFakeResolvedInstanceKey(), $fake);

        app()->bind(static::class, fn () => $instance, true);

        return $instance;
    }

    protected static function getFakeResolvedInstance(): ?MockInterface
    {
        return app(static::getFakeResolvedInstanceKey());
    }

    protected static function getFakeResolvedInstanceKey(): string
    {
        return static::class;
    }
}
