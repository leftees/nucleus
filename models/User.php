<?hh

enum UserState: int {
  Pending = 0;
  Accepted = 1;
  Waitlisted = 2;
  Rejected = 3;
}

class User {
  private int $id = 0;
  private string $email = '';
  private string $fname = '';
  private string $lname = '';
  private string $graduation = '';
  private string $major = '';
  private string $shirt_size = '';
  private string $dietary_restrictions = '';
  private string $special_needs = '';
  private string $birthday = '';
  private string $gender = '';
  private string $phone_number = '';
  private string $school = '';
  private string $created = '';
  private UserState $status = UserState::Pending;
  private array<UserRole> $roles = array();

  public static function create(
    League\OAuth2\Client\Provider\MLHUser $mlh_user
  ): ?User {
    DB::query(
      "SELECT * FROM users WHERE id=%d OR email=%s",
      $mlh_user->getId(), $mlh_user->getEmail()
    );

    if(DB::count() != 0) {
      return null;
    }

    DB::insert('users', array(
      'id' => $mlh_user->getId(),
      'email' => $mlh_user->getEmail(),
      'fname' => $mlh_user->getFirstName(),
      'lname' => $mlh_user->getLastName(),
      'graduation' => $mlh_user->getGraduation(),
      'major' => $mlh_user->getMajor(),
      'shirt_size' => $mlh_user->getShirtSize(),
      'dietary_restrictions' => $mlh_user->getDietaryRestrictions(),
      'special_needs' => $mlh_user->getSpecialNeeds(),
      'birthday' => $mlh_user->getBirthday(),
      'gender' => $mlh_user->getGender(),
      'phone_number' => $mlh_user->getPhoneNumber(),
      'school' => $mlh_user->getSchool(),
      'status' => UserState::Pending,
    ));

    return self::genByID($mlh_user->getId());
  }

  public function getID():int {
    return $this->id;
  }

  public function getEmail(): string {
    return $this->email;
  }

  public function getFirstName(): string {
    return $this->fname;
  }

  public function getLastName(): string {
    return $this->lname;
  }

  public function getRoles(): array<UserRole> {
    return $this->roles;
  }

  public function getStatus(): UserState {
    return $this->status;
  }

  public function getCreated(): string {
    return $this->created;
  }

  public function isPending(): bool {
    return $this->status == UserState::Pending;
  }

  public function isAccepted(): bool {
    return $this->status == UserState::Accepted;
  }

  public function isWaitlisted(): bool {
    return $this->status == UserState::Waitlisted;
  }

  public function isRejected(): bool {
    return $this->status == UserState::Rejected;
  }

  public function delete(): void {
    self::deleteByID($this->id);
  }

  public static function genByID($user_id): ?User {
    return self::constructFromQuery('id', $user_id);
  }

  public static function updateStatusByID(UserState $status, int $user_id): void {
    DB::update('users', array('member_status' => $status), "id=%s", $user_id);
  }

  public static function deleteByID($user_id): void {
    DB::delete('users', 'id=%s', $user_id);
  }

  private static function constructFromQuery($field, $query): ?User {
    # Get the user
    $query = DB::queryFirstRow("SELECT * FROM users WHERE " . $field ."=%s", $query);
    if(!$query) {
      return null;
    }
    $user = self::createFromQuery($query);
    $user->roles = Roles::getRoles($user->getID());

    return $user;
  }

  private static function createFromQuery(array $query): User {
    $user = new User();
    $user->id = (int)$query['id'];
    $user->email = $query['email'];
    $user->fname = $query['fname'];
    $user->lname = $query['lname'];
    $user->graduation = $query['graduation'];
    $user->major = $query['major'];
    $user->shirt_size = $query['shirt_size'];
    $user->dietary_restrictions = $query['dietary_restrictions'];
    $user->special_needs = $query['special_needs'];
    $user->birthday = $query['birthday'];
    $user->gender = $query['gender'];
    $user->phone_number = $query['phone_number'];
    $user->school = $query['school'];
    $user->status = UserState::assert($query['status']);
    $user->created = $query['created'];
    return $user;
  }
}
