import i18n from 'i18next';
import { initReactI18next } from 'react-i18next';

const resources = {
  'pt-BR': {
    translation: {
      // Login Page
      welcome_back: 'Bem-vindo de volta!',
      enter_credentials: 'Insira suas credenciais para gerenciar suas obras.',
      email: 'Email',
      password: 'Senha',
      forgot_password: 'Esqueci minha senha',
      enter: 'Entrar',
      dont_have_account: 'Não tem uma conta?',
      sign_up: 'Cadastre-se',
      forgot_password_title: 'Esqueceu sua senha?',
      enter_email_reset: 'Insira seu email para receber um link de redefinição de senha.',
      send_reset_link: 'Enviar link de redefinição',
      password_reset_sent: 'Link de redefinição enviado para seu email!',
      enter_valid_email: 'Por favor, insira um email válido',
      remember_password: 'Lembrou da senha?',
      back_to_login: 'Voltar para o login',
      
      // Signup Page
      create_account: 'Crie sua conta',
      start_managing: 'Comece a gerenciar seus projetos com eficiência e simplicidade.',
      build_future: 'Construa o futuro da sua gestão.',
      join_professionals: 'Junte-se a milhares de profissionais que simplificam seus projetos conosco.',
      individual: 'Pessoa Física',
      company: 'Pessoa Jurídica',
      full_name: 'Nome Completo',
      corporate_name: 'Razão Social',
      cpf: 'CPF',
      cnpj: 'CNPJ',
      phone: 'Telefone Celular',
      enter_phone: 'Digite seu telefone',
      create_strong_password: 'Crie uma senha forte',
      password_requirements: 'A senha deve ter no mínimo 8 caracteres, incluindo 1 letra maiúscula e 1 símbolo (!@#$%^&*())',
      password_too_short: 'A senha deve ter no mínimo 8 caracteres',
      password_missing_uppercase: 'A senha deve conter pelo menos 1 letra maiúscula',
      password_missing_symbol: 'A senha deve conter pelo menos 1 símbolo (!@#$%^&*())',
      agree_terms: 'Eu li e concordo com os',
      terms_of_service: 'Termos de Serviço',
      and: 'e a',
      privacy_policy: 'Política de Privacidade',
      create_account_button: 'Criar conta',
      already_have_account: 'Já tem uma conta?',
      login_here: 'Faça login',
      
      // Dashboard
      general_view: 'Visão Geral das Obras',
      search_construction: 'Buscar obra...',
      search_by_name: 'Buscar por nome...',
      add_new_construction: 'Adicionar Nova Obra',
      all: 'Todos',
      in_progress: 'Em Andamento',
      completed: 'Concluído',
      delayed: 'Atrasado',
      start_date: 'Início:',
      estimated_time: 'Término Previsto:',
      progress: 'Progresso',
      view_details: 'Ver Detalhes',
      photos: 'Fotos',
      videos: 'Vídeos',
      
      // Sidebar
      dashboard: 'Dashboard',
      constructions: 'Obras',
      reports: 'Relatórios',
      team: 'Equipe',
      settings: 'Configurações',
      help: 'Ajuda',
      logout: 'Sair',
      
      // Sidebar Section Titles
      management: 'GESTÃO',
      registrations: 'CADASTROS',
      customizations: 'PERSONALIZAÇÕES',
      support: 'Suporte',
      configurations: 'CONFIGURAÇÕES',
      
      // Sidebar Menu Items
      budgets: 'Orçamentos',
      reminders: 'Lembretes',
      financial: 'Financeiro',
      client_reports: 'Relatórios do cliente',
      clients: 'Clientes',
      contracts: 'Contratos',
      checklist: 'Checklist',
      equipment: 'Equipamentos',
      suppliers: 'Fornecedores',
      client_report_layout: 'Layout relatório cliente',
      tutorials: 'Tutoriais',
      calculator: 'Calculadora',
      support_chat: 'Suporte Chat',
      my_profile: 'Meu Perfil',
      employees: 'Funcionários',
      subscription: 'Assinatura',
      
      // Sidebar Branding
      app_name: 'Gestão de Obra',
      app_tagline: 'Fácil',

      // Header Bell Reminders Menu (namespaced to avoid collision)
      reminders_menu: {
        menu_title: 'Lembretes',
        menu_loading: 'Carregando...',
        menu_empty_today: 'Sem lembretes para hoje',
        menu_view_all: 'Ver todos os lembretes'
      }
    },
  },
  en: {
    translation: {
      // Login Page
      welcome_back: 'Welcome back!',
      enter_credentials: 'Enter your credentials to manage your construction projects.',
      email: 'Email',
      password: 'Password',
      forgot_password: 'Forgot my password',
      enter: 'Sign In',
      dont_have_account: 'Don\'t have an account?',
      sign_up: 'Sign up',
      forgot_password_title: 'Forgot your password?',
      enter_email_reset: 'Enter your email to receive a password reset link.',
      send_reset_link: 'Send reset link',
      password_reset_sent: 'Password reset link sent to your email!',
      enter_valid_email: 'Please enter a valid email',
      remember_password: 'Remember your password?',
      back_to_login: 'Back to login',
      
      // Signup Page
      create_account: 'Create your account',
      start_managing: 'Start managing your projects with efficiency and simplicity.',
      build_future: 'Build the future of your management.',
      join_professionals: 'Join thousands of professionals who simplify their projects with us.',
      individual: 'Individual',
      company: 'Company',
      full_name: 'Full Name',
      corporate_name: 'Corporate Name',
      cpf: 'Tax ID',
      cnpj: 'Business ID',
      document_number: 'Document Number',
      phone: 'Mobile Phone',
      enter_phone: 'Enter your phone number',
      create_strong_password: 'Create a strong password',
      password_requirements: 'Password must be at least 8 characters long, including 1 uppercase letter and 1 symbol (!@#$%^&*())',
      password_too_short: 'Password must be at least 8 characters long',
      password_missing_uppercase: 'Password must contain at least 1 uppercase letter',
      password_missing_symbol: 'Password must contain at least 1 symbol (!@#$%^&*())',
      agree_terms: 'I have read and agree to the',
      terms_of_service: 'Terms of Service',
      and: 'and',
      privacy_policy: 'Privacy Policy',
      create_account_button: 'Create account',
      already_have_account: 'Already have an account?',
      login_here: 'Sign in',
      
      // Dashboard
      general_view: 'Construction Overview',
      search_construction: 'Search construction...',
      search_by_name: 'Search by name...',
      add_new_construction: 'Add New Construction',
      all: 'All',
      in_progress: 'In Progress',
      completed: 'Completed',
      delayed: 'Delayed',
      start_date: 'Start:',
      estimated_time: 'Estimated Completion:',
      progress: 'Progress',
      view_details: 'View Details',
      photos: 'Photos',
      videos: 'Videos',
      
      // Sidebar
      dashboard: 'Dashboard',
      constructions: 'Constructions',
      reports: 'Reports',
      team: 'Team',
      settings: 'Settings',
      help: 'Help',
      logout: 'Logout',
      
      // Sidebar Section Titles
      management: 'MANAGEMENT',
      registrations: 'REGISTRATIONS',
      customizations: 'CUSTOMIZATIONS',
      support: 'Support',
      configurations: 'CONFIGURATIONS',
      
      // Sidebar Menu Items
      budgets: 'Budgets',
      reminders: 'Reminders',
      financial: 'Financial',
      client_reports: 'Client Reports',
      clients: 'Clients',
      contracts: 'Contracts',
      checklist: 'Checklist',
      equipment: 'Equipment',
      suppliers: 'Suppliers',
      client_report_layout: 'Client Report Layout',
      tutorials: 'Tutorials',
      calculator: 'Calculator',
      support_chat: 'Support Chat',
      my_profile: 'My Profile',
      employees: 'Employees',
      subscription: 'Subscription',

      // Header Bell Reminders Menu (namespaced to avoid collision)
      reminders_menu: {
        menu_title: 'Reminders',
        menu_loading: 'Loading...',
        menu_empty_today: 'No reminders for today',
        menu_view_all: 'View all reminders'
      }
    },
  },
  es: {
    translation: {
      // Login Page
      welcome_back: '¡Bienvenido de nuevo!',
      enter_credentials: 'Ingrese sus credenciales para gestionar sus obras.',
      email: 'Correo electrónico',
      password: 'Contraseña',
      forgot_password: 'Olvidé mi contraseña',
      enter: 'Entrar',
      dont_have_account: '¿No tienes una cuenta?',
      sign_up: 'Regístrate',
      forgot_password_title: '¿Olvidaste tu contraseña?',
      enter_email_reset: 'Ingresa tu email para recibir un enlace de restablecimiento de contraseña.',
      send_reset_link: 'Enviar enlace de restablecimiento',
      password_reset_sent: '¡Enlace de restablecimiento enviado a tu email!',
      enter_valid_email: 'Por favor, ingresa un email válido',
      remember_password: '¿Recuerdas tu contraseña?',
      back_to_login: 'Volver al inicio de sesión',
      
      // Signup Page
      create_account: 'Crea tu cuenta',
      start_managing: 'Comienza a gestionar tus proyectos con eficiencia y simplicidad.',
      build_future: 'Construye el futuro de tu gestión.',
      join_professionals: 'Únete a miles de profesionales que simplifican sus proyectos con nosotros.',
      individual: 'Persona Física',
      company: 'Persona Jurídica',
      full_name: 'Nombre Completo',
      corporate_name: 'Razón Social',
      cpf: 'ID Tributario',
      cnpj: 'ID Empresarial',
      document_number: 'Número de Documento',
      phone: 'Teléfono Móvil',
      enter_phone: 'Ingresa tu número de teléfono',
      create_strong_password: 'Crea una contraseña fuerte',
      password_requirements: 'La contraseña debe tener al menos 8 caracteres, incluyendo 1 letra mayúscula y 1 símbolo (!@#$%^&*())',
      password_too_short: 'La contraseña debe tener al menos 8 caracteres',
      password_missing_uppercase: 'La contraseña debe contener al menos 1 letra mayúscula',
      password_missing_symbol: 'La contraseña debe contener al menos 1 símbolo (!@#$%^&*())',
      agree_terms: 'He leído y acepto los',
      terms_of_service: 'Términos de Servicio',
      and: 'y la',
      privacy_policy: 'Política de Privacidad',
      create_account_button: 'Crear cuenta',
      already_have_account: '¿Ya tienes una cuenta?',
      login_here: 'Iniciar sesión',
      
      // Dashboard
      general_view: 'Vista General de Obras',
      search_construction: 'Buscar obra...',
      search_by_name: 'Buscar por nombre...',
      add_new_construction: 'Agregar Nueva Obra',
      all: 'Todos',
      in_progress: 'En Progreso',
      completed: 'Completado',
      delayed: 'Retrasado',
      start_date: 'Inicio:',
      estimated_time: 'Finalización Prevista:',
      progress: 'Progreso',
      view_details: 'Ver Detalles',
      photos: 'Fotos',
      videos: 'Videos',
      
      // Sidebar
      dashboard: 'Panel',
      constructions: 'Obras',
      reports: 'Informes',
      team: 'Equipo',
      settings: 'Configuración',
      help: 'Ayuda',
      logout: 'Salir',
      
      // Sidebar Section Titles
      management: 'GESTIÓN',
      registrations: 'REGISTROS',
      customizations: 'PERSONALIZACIONES',
      support: 'Soporte',
      configurations: 'CONFIGURACIONES',
      
      // Sidebar Menu Items
      budgets: 'Presupuestos',
      reminders: 'Recordatorios',
      financial: 'Financiero',
      client_reports: 'Informes del cliente',
      clients: 'Clientes',
      contracts: 'Contratos',
      checklist: 'Lista de verificación',
      equipment: 'Equipos',
      suppliers: 'Proveedores',
      client_report_layout: 'Diseño informe cliente',
      tutorials: 'Tutoriales',
      calculator: 'Calculadora',
      support_chat: 'Chat de Soporte',
      my_profile: 'Mi Perfil',
      employees: 'Empleados',
      subscription: 'Suscripción',
      
      // Sidebar Branding
      app_name: 'Gestão de Obra',
      app_tagline: 'Fácil',

      // Header Bell Reminders Menu (namespaced to avoid collision)
      reminders_menu: {
        menu_title: 'Recordatorios',
        menu_loading: 'Cargando...',
        menu_empty_today: 'Sin recordatorios para hoy',
        menu_view_all: 'Ver todos los recordatorios'
      }
    },
  },
};

// Detect language from IP (simplified - in production use a geolocation API)
const detectLanguageFromIP = async (): Promise<string> => {
  // Evitar chamadas externas em ambiente de desenvolvimento/local
  const host = typeof window !== 'undefined' ? window.location.hostname : '';
  if (host === 'localhost' || host === '127.0.0.1') {
    return 'pt-BR';
  }

  // Em produção sob /ob2 em gestaodeobrafacil.com, evitar chamada externa
  try {
    const baseUrl = (import.meta as any)?.env?.BASE_URL ?? '/';
    const pathname = typeof window !== 'undefined' ? window.location.pathname : '';
    if (host.includes('gestaodeobrafacil.com') && baseUrl === '/ob2/' && pathname.startsWith('/ob2')) {
      return 'pt-BR';
    }
  } catch {
    // Ignorar erros de acesso a import.meta em ambientes não suportados
  }

  try {
    const controller = new AbortController();
    const timeout = setTimeout(() => controller.abort(), 2500);
    const response = await fetch('https://ipapi.co/json/', { signal: controller.signal });
    clearTimeout(timeout);

    if (!response.ok) {
      return 'pt-BR';
    }

    const data = await response.json();
    const countryCode = data.country_code;
    
    if (countryCode === 'BR') return 'pt-BR';
    if (countryCode === 'ES' || countryCode === 'MX' || countryCode === 'AR' || 
        countryCode === 'CO' || countryCode === 'CL' || countryCode === 'PE') return 'es';
    return 'en';
  } catch (error) {
    return 'pt-BR'; // Fallback seguro
  }
};

// Initialize i18n
export const initI18n = async () => {
  const detectedLanguage = await detectLanguageFromIP();
  const storedLanguage = localStorage.getItem('language');
  const language = storedLanguage || detectedLanguage;

  await i18n
    .use(initReactI18next)
    .init({
      resources,
      lng: language,
      fallbackLng: 'pt-BR',
      interpolation: {
        escapeValue: false,
      },
    });

  return language;
};

export default i18n;
