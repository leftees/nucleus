<?hh

class Login {
  public static function get(): :xhp {
    # Check to see if we're going to perform an action
    parse_str($_SERVER['QUERY_STRING'], $query_params);
    if(isset($query_params['action'])) {
      # Log the user out
      if($query_params['action'] === 'logout') {
        Auth::logout();
        header('Location: /');
      }

      # Refresh the session user
      if($query_params['action'] === 'refresh') {
        $user = Session::getUser();
        Auth::logout();
        $user = User::genByID($user->getID());
        Session::create($user);
      }
    }

    if(Session::isActive()) {
      header('Location: /dashboard');
    }

    return
      <div class="well col-md-4 col-md-offset-4">
        <form method="post" action="/login">
          <div class="form-group">
            <label>Username</label>
            <input type="text" class="form-control" name="username" placeholder="Username" />
          </div>
          <div class="form-group">
            <label>Password</label>
            <input type="password" class="form-control" name="password" placeholder="Password" />
          </div>
          <button type="submit" class="btn btn-default">Submit</button>
        </form>
      </div>;
  }

  public static function post(): void {
    # Make sure all required fields were filled out
    if(!isset($_POST['username']) || !isset($_POST['password'])) {
      header('Location: /login');
      return;
    }

    # Authenticate
    Auth::login($_POST['username'], $_POST['password']);

    # Redirect to where we need to go
    $user = Session::getUser();
    if(!$user) {
      header('Location: /login');
    } else {
      header('Location: /dashboard');
    }
  }
}