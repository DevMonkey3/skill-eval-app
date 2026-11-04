import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { ComputedRef } from 'vue';

/**
 * ログイン中のユーザーが管理者か (社員・スキルを編集できるか)。
 * ボタンを出すかどうかの判定だけで、本当の権限チェックはサーバー側の Gate 'admin' が行う。
 */
export function useIsAdmin(): ComputedRef<boolean> {
    const page = usePage();

    return computed(() => page.props.auth.user?.is_admin === true);
}
