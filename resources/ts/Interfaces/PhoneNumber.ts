import {Contact} from "./Contact";
export interface PhoneNumber {
    id: number
    contact_id: number
    value: string
    is_landline: boolean
    can_receive_text: boolean
    has_whatsapp: boolean
    blocked_at: string
    created_at: string
    updated_at: string
    contact?: Contact
}
