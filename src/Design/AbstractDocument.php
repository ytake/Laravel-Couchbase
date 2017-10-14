<?php
declare(strict_types=1);

namespace Ytake\LaravelCouchbase\Design;

/**
 * Class AbstractDocument
 */
abstract class AbstractDocument
{
    /** @var string */
    private $name;

    /**
     * AbstractDocument constructor.
     *
     * @param string $name
     */
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    abstract protected function document(): string;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return json_encode([
            $this->name => [
                'map' => $this->document(),
            ],
        ]);
    }
}
