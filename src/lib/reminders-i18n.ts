import i18n from "./i18n";

// Namespace exclusivo para a página de Lembretes
export const REMINDERS_NS = "reminders" as const;

const resources = {
  "pt-BR": {
    page_title: "Lembretes",
    search_placeholder: "Buscar por título ou obra...",
    add_button: "Novo Lembrete",
    new_title: "Novo Lembrete",
    edit_title: "Editar Lembrete",
    view_title: "Visualizar Lembrete",
    error_loading: "Falha ao carregar lembrete",
    actions: {
      save: "Salvar",
      cancel: "Cancelar",
    },
    delete_confirm_title: "Excluir lembrete",
    delete_confirm_description: "Tem certeza que deseja excluir este lembrete? Esta ação não pode ser desfeita.",
    delete_confirm_ok: "Excluir",
    fields: {
      title: "Título",
      description: "Descrição",
      date: "Data",
      time: "Hora",
      priority: "Prioridade",
      type: "Tipo",
      work_id: "ID da Obra",
      work: "Obra",
    },
    select_work_placeholder: "Selecione a obra (opcional)",
    priority: {
      low: "Baixa",
      medium: "Média",
      high: "Alta",
    },
    type: {
      general: "Geral",
      work: "Obra",
      financial: "Financeiro",
      meeting: "Reunião",
      deadline: "Prazo",
    },
    empty: "Nenhum lembrete encontrado.",
    error_loading: "Erro ao carregar lembretes.",
    columns: {
      title: "Título",
      work: "Obra",
      created_at: "Criado em",
      due_date: "Vencimento",
      status: "Status",
      actions: "Ações",
    },
    status: {
      pending: "Pendente",
      done: "Concluído",
      overdue: "Vencido",
      canceled: "Cancelado",
    },
  },
  en: {
    page_title: "Reminders",
    search_placeholder: "Search by title or work...",
    add_button: "New Reminder",
    new_title: "New Reminder",
    edit_title: "Edit Reminder",
    view_title: "View Reminder",
    error_loading: "Failed to load reminder",
    actions: {
      save: "Save",
      cancel: "Cancel",
    },
    delete_confirm_title: "Delete reminder",
    delete_confirm_description: "Are you sure you want to delete this reminder? This action cannot be undone.",
    delete_confirm_ok: "Delete",
    fields: {
      title: "Title",
      description: "Description",
      date: "Date",
      time: "Time",
      priority: "Priority",
      type: "Type",
      work_id: "Work ID",
      work: "Work",
    },
    select_work_placeholder: "Select a work (optional)",
    priority: {
      low: "Low",
      medium: "Medium",
      high: "High",
    },
    type: {
      general: "General",
      work: "Work",
      financial: "Financial",
      meeting: "Meeting",
      deadline: "Deadline",
    },
    empty: "No reminders found.",
    error_loading: "Error loading reminders.",
    columns: {
      title: "Title",
      work: "Work",
      created_at: "Created At",
      due_date: "Due Date",
      status: "Status",
      actions: "Actions",
    },
    status: {
      pending: "Pending",
      done: "Done",
      overdue: "Overdue",
      canceled: "Canceled",
    },
  },
  es: {
    page_title: "Recordatorios",
    search_placeholder: "Buscar por título u obra...",
    add_button: "Nuevo Recordatorio",
    new_title: "Nuevo Recordatorio",
    edit_title: "Editar Recordatorio",
    view_title: "Ver Recordatorio",
    error_loading: "Error al cargar el recordatorio",
    actions: {
      save: "Guardar",
      cancel: "Cancelar",
    },
    delete_confirm_title: "Eliminar recordatorio",
    delete_confirm_description: "¿Está seguro de que desea eliminar este recordatorio? Esta acción no se puede deshacer.",
    delete_confirm_ok: "Eliminar",
    fields: {
      title: "Título",
      description: "Descripción",
      date: "Fecha",
      time: "Hora",
      priority: "Prioridad",
      type: "Tipo",
      work_id: "ID de Obra",
      work: "Obra",
    },
    select_work_placeholder: "Seleccione la obra (opcional)",
    priority: {
      low: "Baja",
      medium: "Media",
      high: "Alta",
    },
    type: {
      general: "General",
      work: "Obra",
      financial: "Financiero",
      meeting: "Reunión",
      deadline: "Plazo",
    },
    empty: "No se encontraron recordatorios.",
    error_loading: "Error al cargar los recordatorios.",
    columns: {
      title: "Título",
      work: "Obra",
      created_at: "Creado el",
      due_date: "Vencimiento",
      status: "Estado",
      actions: "Acciones",
    },
    status: {
      pending: "Pendiente",
      done: "Completado",
      overdue: "Vencido",
      canceled: "Cancelado",
    },
  },
};

// Registra/atualiza o namespace para os idiomas suportados
export function ensureRemindersI18n() {
  const langs = Object.keys(resources);
  for (const lang of langs) {
    const bundle = (resources as any)[lang];
    i18n.addResourceBundle(lang, REMINDERS_NS, bundle, true, true);
  }
}

export default resources;