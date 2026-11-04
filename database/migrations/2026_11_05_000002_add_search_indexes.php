<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * PostgreSQL は外部キーに自動でインデックスを作らないので、検索・集計で使う列に追加する。
     */
    public function up(): void
    {
        // スキル一覧・ダッシュボードの withCount('employees') と、スキル削除時の cascade で使う
        Schema::table('employee_skill', function (Blueprint $table) {
            $table->index('skill_id');
        });

        Schema::table('evaluations', function (Blueprint $table) {
            // 評価者のユーザーを削除したときの cascade で使う
            $table->index('evaluator_id');

            // 評価一覧は「期間で絞り込み → 新しい順」なので、period 単体より複合インデックスの方が効く
            $table->dropIndex(['period']);
            $table->index(['period', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::table('evaluations', function (Blueprint $table) {
            $table->dropIndex(['period', 'created_at']);
            $table->index('period');
            $table->dropIndex(['evaluator_id']);
        });

        Schema::table('employee_skill', function (Blueprint $table) {
            $table->dropIndex(['skill_id']);
        });
    }
};
