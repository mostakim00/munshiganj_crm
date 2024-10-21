<?php
namespace App\Http\Traits;
use Intervention\Image\Facades\Image;

trait UserDataTraits{

    public function getUserData($user)
    {
        $userData    = $user->projects;
        $superAdmin  = $user->hasAnyRole('Super Admin');
        $admin       = $user->hasAnyRole('Admin');

        return compact('userData', 'superAdmin', 'admin');
    }


}

?>
