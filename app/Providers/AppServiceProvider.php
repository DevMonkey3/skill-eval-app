<?php

namespace App\Providers;

use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Validation\Rules\Password;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureDefaults();
        $this->configureAuthorization();
        $this->configureRouting();
    }

    /**
     * 社員・スキルの登録 / 編集 / 削除は管理者だけ。閲覧と評価の登録は全員ができる。
     */
    protected function configureAuthorization(): void
    {
        Gate::define('admin', fn (User $user): bool => $user->is_admin);
    }

    protected function configureRouting(): void
    {
        // /employees/abc のような ID は DB に問い合わせる前に 404 にする
        // (PostgreSQL では bigint の列を文字列で検索するとエラーになるため)
        Route::patterns([
            'employee' => '[0-9]+',
            'skill' => '[0-9]+',
            'evaluation' => '[0-9]+',
        ]);

        // API 全体の回数制限。ログイン中はユーザーごと、未ログインは IP ごとに 1 分 60 回
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(60)
            ->by((string) ($request->user()?->getAuthIdentifier() ?? $request->ip())));
    }

    /**
     * Configure default behaviors for production-ready applications.
     */
    protected function configureDefaults(): void
    {
        Date::use(CarbonImmutable::class);

        // 開発中に with() / load() の付け忘れ (N+1) を例外で気づけるようにする
        Model::preventLazyLoading(! app()->isProduction());

        DB::prohibitDestructiveCommands(
            app()->isProduction(),
        );

        Password::defaults(fn (): ?Password => app()->isProduction()
            ? Password::min(12)
                ->mixedCase()
                ->letters()
                ->numbers()
                ->symbols()
                ->uncompromised()
            : null,
        );
    }
}
