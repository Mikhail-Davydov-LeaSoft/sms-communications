import {MessageData} from "./MessageData";
import {PhoneNumber} from "./PhoneNumber";
import {AccountPhoneNumber} from "./AccountPhoneNumber";
export interface ConversationData {
    id: number
    account_phone_number_id: number
    phone_number_id: number
    created_at: string
    updated_at: string
    messages: Array<MessageData>
    phone_number: PhoneNumber
    account_phone_number: AccountPhoneNumber
}
