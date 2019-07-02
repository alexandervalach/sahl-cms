<?php

declare(strict_types=1);

namespace App\Model;

use Nette;
use Nette\Security\Identity;
use Nette\Security\AuthenticationException;
use Nette\Security\Passwords;
use Nette\Security\IIdentity;
use Nette\Security\IAuthenticator;

/**
 * Users management.
 */
class UserManager implements IAuthenticator
{
  use Nette\SmartObject;

  const COLUMN_NAME = 'username';
  const COLUMN_PASSWORD_HASH = 'password';
  const COLUMN_ID = 'id';
  const COLUMN_ROLE = 'role';

  /** @var UsersRepository */
  private $usersRepository;

  /** @var Passwords */
  private $passwords;

  public function __construct(UsersRepository $usersRepository, Passwords $passwords) {
    $this->usersRepository = $usersRepository;
    $this->passwords = $passwords;
  }

  /**
   * Performs an authentication.
   * @throws AuthenticationException
   */
  public function authenticate(array $credentials): IIdentity
  {
    [$username, $password] = $credentials;

    $row = $this->usersRepository->findByValue(self::COLUMN_NAME, $username)->fetch();

    if (!$row) {
      throw new AuthenticationException('Používateľ neexistuje.', self::IDENTITY_NOT_FOUND);
    } elseif (!$this->passwords->verify($password, $row[self::COLUMN_PASSWORD_HASH])) {
      throw new AuthenticationException('Nesprávne heslo', self::INVALID_CREDENTIAL);
    } elseif ($this->passwords->needsRehash($row[self::COLUMN_PASSWORD_HASH])) {
      $row->update([
			  self::COLUMN_PASSWORD_HASH => $this->passwords->hash($password),
			]);
    }

    $arr = $row->toArray();
    unset($arr[self::COLUMN_PASSWORD_HASH]);
    return new Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
  }
}
