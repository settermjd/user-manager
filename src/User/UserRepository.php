<?php

declare(strict_types=1);

namespace UserManager\User;

use Axleus\Db;
use Axleus\Db\EntityInterface;
use Laminas\Db\ResultSet\AbstractResultSet; // do not remove
use Laminas\Db\Sql\Select;
use Laminas\Db\Sql\Where;
use Mezzio\Authentication\Exception;
use Mezzio\Authentication\UserInterface;
use Mezzio\Authentication\UserRepositoryInterface;
use Webmozart\Assert\Assert;

use function password_verify;

final class UserRepository extends Db\AbstractRepository implements UserRepositoryInterface
{
    /**
     * @var callable
     * @psalm-var callable(string, array<int|string, string>, array<string, mixed>): UserInterface
     */
    private $userFactory;

    public function __construct(
        protected Db\TableGateway $gateway,
        callable $userFactory,
        protected $hydrator,
        private array $config = []
    ) {
        parent::__construct($gateway, $hydrator);
        // Provide type safety for the composed user factory.
        $this->userFactory = static function (
            string $identity,
            array $roles = [],
            array $details = []
        ) use ($userFactory): UserInterface {
            Assert::allString($roles);
            Assert::isMap($details);

            return $userFactory($identity, $roles, $details);
        };
    }

    public function authenticate(string $credential, ?string $password = null): ?UserInterface
    {
        $select = $this->gateway->getSql()->select();
        $where = new Where();
        $where->equalTo($this->config['username'], $credential);
        $where->isNotNull('verified');
        $select->where($where);
        /** @var ResultSetInterface */
        $resultSet = $this->gateway->selectWith($select);
        $user = $resultSet->current();
        $hash = $user->getPassword();
        $this->checkBcryptHash($hash);
        if (password_verify($password, $hash)) {
            return ($this->userFactory)(
                $credential,
                $user->getRoles(),
                $user->getDetails()
            );
        }
        return null;
    }

    public function findOneBy(string $column, mixed $value, ?array $columns = [Select::SQL_STAR], ?array $joins = null): ?EntityInterface
    {
        $select = $this->gateway->getSql()->select();
        $where = new Where();
        $where->equalTo($column, $value);
        $select->where($where);
        /** @var ResultSetInterface */
        $resultSet = $this->gateway->selectWith($select);
        // todo: add exception handling
        return $resultSet->current();
    }

    /**
     * Check bcrypt usage for security reason
     *
     * @throws Exception\RuntimeException
     */
    protected function checkBcryptHash(string $hash): void
    {
        if (0 !== strpos($hash, '$2y$')) {
            throw new Exception\RuntimeException(
                'The provided hash has not been created with a supported algorithm. Please use bcrypt.'
            );
        }
    }

    public function getTable(): string
    {
        return $this->gateway->getTable();
    }
}
