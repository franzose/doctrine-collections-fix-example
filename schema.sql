create sequence public.category_id_seq
    increment by 1;

create table if not exists public.category
(
    id         bigint not null primary key,
    parent_id  bigint constraint fk_category_category_id references public.category,
    uid        text not null,
    name       text not null,
    created_at timestamp(0) with time zone not null,
    updated_at timestamp(0) with time zone not null
);

create unique index uniq_category_uid
    on public.category (uid);
