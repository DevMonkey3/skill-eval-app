// Laravel のモデルが JSON になったときの形

export type Skill = {
    id: number;
    name: string;
    category: string;
    employees_count?: number;
    average_level?: number | string | null; // PostgreSQL の avg() は文字列で返る
    pivot?: { level: number };
};

export type Evaluation = {
    id: number;
    employee_id: number;
    evaluator_id: number;
    period: string;
    score: number;
    comment: string | null;
    created_at: string;
    employee?: Pick<Employee, 'id' | 'name' | 'employee_code'>;
    evaluator?: { id: number; name: string };
};

export type Employee = {
    id: number;
    employee_code: string;
    name: string;
    email: string;
    department: string;
    position: string;
    hired_on: string | null;
    skills_count?: number;
    skills?: Skill[];
    evaluations?: Evaluation[];
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

// paginate() の戻り値 (LengthAwarePaginator) の JSON
export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

// PHP の [1 => '入門', ...] は JSON だと { "1": "入門", ... } になる
export type Labels = Record<string, string>;
