import {Account} from "./Account";
export interface AccountPhoneNumber {
    id: number
    account_id: number
    value: string
    created_at: string
    updated_at: string
    account?: Account
}
