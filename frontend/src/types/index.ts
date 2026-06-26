export type ActStatus = 'not_sent' | 'awaiting_signature' | 'closed' | 'needs_attention'

export interface DashboardSummary {
  total_amount: number
  projects_count: number
  payments_count: number
  closed_acts_amount: number
  unclosed_acts_amount: number
  unsent_acts_count: number
  awaiting_signature_count: number
  needs_attention_count: number
  sent_acts_amount: number
}

export interface PaymentAct {
  is_sent: boolean
  is_signed: boolean
  sent_at: string | null
  signed_at: string | null
  manager_comment: string | null
  status: ActStatus
  status_label: string
}

export interface Payment {
  id: number
  external_id: string
  payment_date: string
  payment_date_ru: string
  project_id: number
  project_name: string
  legal_entity_id: number
  legal_entity: {
    name: string
    legal_type: string
    inn: string
    ogrn: string
    bank_account: string
    bank_details: string
  }
  amount: number
  payment_purpose: string
  service_stage: string
  period: string | null
  document_number: string | null
  invoice_reference: string | null
  is_confirmed: boolean
  act: PaymentAct
}

export interface ProjectAggregate {
  id: number
  name: string
  legal_entity: {
    id: number
    name: string
    legal_type: string
    inn: string
    ogrn: string
    bank_account: string
  }
  payments_count: number
  total_amount: number
  closed_acts_count: number
  unclosed_acts_count: number
  sent_acts_amount: number
  signed_acts_amount: number
  document_flow_status: ActStatus
  document_flow_status_label: string
  closure_percent: number
  service_stages: string[]
}

export interface FilterOptions {
  projects: { id: number; name: string; legal_entity_id: number }[]
  legal_entities: { id: number; name: string; inn: string }[]
  service_stages: string[]
  date_from: string
  date_to: string
  act_statuses: { value: ActStatus; label: string }[]
  statement_period: { from: string; to: string }
}

export interface DashboardFilters {
  search: string
  project_id: string
  service_stage: string
  date_from: string
  date_to: string
  is_sent: string
  is_signed: string
  act_status: string
}
