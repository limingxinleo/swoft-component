<?php declare(strict_types=1);


namespace Swoft\Context;


use Swoft\Co;
use Swoft\Http\Server\HttpContext;

/**
 * Class Context
 *
 * @since 2.0
 */
class Context
{
    /**
     * Context
     *
     * @var array
     *
     * @example
     * [
     *    'tid' => ContextInterface,
     *    'tid2' => ContextInterface,
     *    'tid3' => ContextInterface,
     * ]
     */
    private static $context = [];

    /**
     * Get context
     *
     * @return ContextInterface|HttpContext
     */
    public static function get(): ContextInterface
    {
        $tid = Co::tid();

        return self::$context[$tid];
    }

    /**
     * Set context
     *
     * @param ContextInterface $context
     */
    public static function set(ContextInterface $context)
    {
        $tid = Co::tid();

        self::$context[$tid] = $context;
    }

    /**
     * Destroy context
     */
    public static function destroy()
    {
        $tid = Co::tid();
        unset(self::$context[$tid]);
    }
}