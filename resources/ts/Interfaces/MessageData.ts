export interface MessageData {
    id: number
    conversation_id: number
    message_type: string
    file_name: string|null
    status: string
    service_message_id: string
    is_incoming: boolean
    is_unread: boolean
    is_pinned: boolean
    body: string|null
    user_id: number|null
    created_at: string
    updated_at: string
}
