export interface Account {
    id: number
    name: string
    email: string
    identifier: string
    credentials: string
    created_at: string
    updated_at: string
    account_phone_numbers?: string
}
