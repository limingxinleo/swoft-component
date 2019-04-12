<?php declare(strict_types=1);


namespace Swoft\Db\Connector;


use Swoft\Bean\Annotation\Mapping\Bean;

/**
 * Class MySqlConnector
 *
 * @Bean()
 * @since 2.0
 */
class MySqlConnector extends AbstractConnector
{
    /**
     * Establish a database connection.
     *
     * @param array $config
     *
     * @return \PDO
     * @throws \Exception
     */
    public function connect(array $config): \PDO
    {
        $dsn      = $config['dsn'];
        $username = $config['username'];
        $password = $config['password'];
        $options  = $config['options'];

        // We need to grab the PDO options that should be used while making the brand
        // new connection instance. The PDO options control various aspects of the
        // connection's behavior, and some might be specified by the developers.
        $connection = $this->createConnection($dsn, $username, $password, $options);

        if (!empty($config['database'])) {
            $connection->exec("use `{$config['database']}`;");
        }

        $this->configureEncoding($connection, $config);

        // Next, we will check to see if a timezone has been specified in this config
        // and if it has we will issue a statement to modify the timezone with the
        // database. Setting this DB timezone is an optional configuration item.
        $this->configureTimezone($connection, $config);

        $this->setModes($connection, $config);

        return $connection;
    }

    /**
     * Set the connection character set and collation.
     *
     * @param \PDO  $connection
     * @param array $config
     */
    protected function configureEncoding(\PDO $connection, array $config): void
    {
        $charset = $config['charset'];
        $config  = $config['config'];
        if (empty($charset)) {
            return;
        }

        $collation = $config['collation'] ?? '';
        $collation = !empty($collation) ? sprintf('collate %s', $collation) : '';

        $connection->prepare(sprintf('set names %s %s', $charset, $collation))->execute();
    }

    /**
     * Set the timezone on the connection.
     *
     * @param  \PDO  $connection
     * @param  array $config
     *
     * @return void
     */
    protected function configureTimezone($connection, array $config): void
    {
        $config   = $config['config'];
        $timezone = $config['timezone'] ?? '';
        if (!empty($timezone)) {
            $connection->prepare(sprintf('set time_zone="%s"', $timezone), $timezone)->execute();
        }
    }

    /**
     * Determine if the given configuration array has a UNIX socket value.
     *
     * @param  array $config
     *
     * @return bool
     */
    protected function hasSocket(array $config)
    {
        return isset($config['unix_socket']) && !empty($config['unix_socket']);
    }

    /**
     * Get the DSN string for a socket configuration.
     *
     * @param  array $config
     *
     * @return string
     */
    protected function getSocketDsn(array $config)
    {
        return "mysql:unix_socket={$config['unix_socket']};dbname={$config['database']}";
    }

    /**
     * Set the modes for the connection.
     *
     * @param  \PDO  $connection
     * @param  array $config
     *
     * @return void
     */
    protected function setModes(\PDO $connection, array $config): void
    {
        $modes  = $config['modes'] ?? [];
        $config = $config['config'];
        if (!empty($modes)) {
            $modes = implode(',', $modes);
            $connection->prepare(sprintf('set session sql_mode="%s"', $modes))->execute();
            return;
        }

        if (isset($config['strict'])) {
            if ($config['strict']) {
                $connection->prepare($this->strictMode($connection))->execute();
            } else {
                $connection->prepare("set session sql_mode='NO_ENGINE_SUBSTITUTION'")->execute();
            }
        }
    }

    /**
     * Get the query to enable strict mode.
     *
     * @param  \PDO $connection
     *
     * @return string
     */
    protected function strictMode(\PDO $connection)
    {
        if (version_compare($connection->getAttribute(\PDO::ATTR_SERVER_VERSION), '8.0.11') >= 0) {
            return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION'";
        }

        return "set session sql_mode='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION'";
    }
}