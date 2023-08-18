<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\Module;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        User::class => UserPolicy::class,

    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        /* lay danh sach Modules */
        $moduleList = Module::all();
        if ($moduleList->count()>0){
            foreach ($moduleList as $module){
                Gate::define($module->name,function (User $user) use ($module){
                    $roleJson = @$user->group()->permissions;
                    if (!empty($roleJson)){
                        $roleArr = json_decode($roleJson,true);
                        $check = isRole($roleArr,$module->name);
                        return $check;
                    }
                    return false;
                });
            }
        }

    }
}
