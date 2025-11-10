import { useEffect, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { NavLink } from '@/components/NavLink';
import {
  Construction,
  Home,
  Users,
  FileText,
  Settings,
  HelpCircle,
  LogOut,
  DollarSign,
  Bell,
  ClipboardList,
  Wrench,
  Truck,
  LineChart,
  Calculator,
  MessageSquare,
  PlayCircle,
  UserCircle2,
  UserCog,
  CreditCard,
  X,
} from 'lucide-react';

export const Sidebar = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [subscriptionType, setSubscriptionType] = useState<string>('');
  const [isMobileOpen, setIsMobileOpen] = useState(false);

  useEffect(() => {
    const userId = localStorage.getItem('userId');
    const token = localStorage.getItem('userToken');
    if (!userId) return;

    const controller = new AbortController();
    const qs = `?usuario_id=${encodeURIComponent(userId)}`;
    fetch(`/appfacil/assinaturas.php${qs}`, {
      method: 'GET',
      headers: token ? { Authorization: `Bearer ${token}` } : {},
      signal: controller.signal,
    })
      .then((res) => res.json())
      .then((data) => {
        // Estrutura esperada: { success, message, data: [], stats }
        if (data && Array.isArray(data.data) && data.data.length) {
          // Preferir assinatura válida (ativa/trial e não expirada)
          const valid = data.data.find((a: any) => a.is_valid);
          const chosen = valid || data.data[0];
          setSubscriptionType(String(chosen.tipo_assinatura || '').toLowerCase());
        } else if (data?.stats?.user_has_trial) {
          setSubscriptionType('trial');
        } else {
          setSubscriptionType('');
        }
      })
      .catch(() => {
        // Silencioso: mantém fallback
      });

    return () => controller.abort();
  }, []);

  // Ouvir evento global para abrir/fechar/toggle a sidebar no mobile
  useEffect(() => {
    const handler = (e: Event) => {
      const detail = (e as CustomEvent).detail as 'open' | 'close' | 'toggle' | undefined;
      setIsMobileOpen((prev) => (detail === 'open' ? true : detail === 'close' ? false : !prev));
    };
    window.addEventListener('toggle-sidebar', handler as EventListener);
    return () => window.removeEventListener('toggle-sidebar', handler as EventListener);
  }, []);

  const handleLogout = () => {
    localStorage.removeItem('isAuthenticated');
    navigate('/login');
  };

  const sections = [
    {
      title: t('management'),
      items: [
        { icon: Home, label: t('constructions'), path: '/dashboard' },
        { icon: FileText, label: t('budgets'), path: '/orcamentos' },
        { icon: Bell, label: t('reminders'), path: '/lembretes' },
        { icon: DollarSign, label: t('financial'), path: '/financial' },
        { icon: LineChart, label: t('reports'), path: '/reports' },
        { icon: Users, label: t('client_reports'), path: '/relatorios-cliente' },
      ],
    },
    {
      title: t('registrations'),
      items: [
        { icon: Users, label: t('clients'), path: '/clientes' },
        { icon: FileText, label: t('contracts'), path: '/contratos' },
        { icon: ClipboardList, label: t('checklist'), path: '/checklist' },
        { icon: Users, label: t('team'), path: '/equipe' },
        { icon: Wrench, label: t('equipment'), path: '/equipamentos' },
        { icon: Truck, label: t('suppliers'), path: '/fornecedores' },
      ],
    },
  
    {
      title: t('support'),
      items: [
        { icon: PlayCircle, label: t('tutorials'), path: '/tutoriais' },
        { icon: Calculator, label: t('calculator'), path: '/calculadora' },
        { icon: MessageSquare, label: t('support_chat'), path: '/suporte-chat' },
      ],
    },
      {
      title: t('customizations'),
      items: [
        { icon: FileText, label: t('client_report_layout'), path: '/layout-relatorio-cliente' },
      ],
    },
    {
      title: t('configurations'),
      items: [
        { icon: UserCircle2, label: t('my_profile'), path: '/perfil' },
        { icon: Users, label: t('employees'), path: '/funcionarios' },
        { icon: CreditCard, label: t('subscription'), path: '/assinatura' },
      ],
    },
  ];

  return (
    <>
    {/* Sidebar desktop */}
    <aside className="hidden md:flex md:w-64 bg-card border-r border-border flex-col">
      <div className="p-6 border-b border-border">
        <div className="flex items-center gap-2">
          <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
            <Construction className="h-6 w-6 text-primary" />
          </div>
          <div className="flex-1">
            <h2 className="font-semibold text-sm">{`${t('app_name')} ${t('app_tagline')}`}</h2>
            <p className="text-xs text-muted-foreground">{subscriptionType || '-'}</p>
          </div>
        </div>
      </div>

      <nav className="flex-1 p-4 space-y-6">
        {sections.map((section) => (
          <div key={section.title}>
            <div className="px-4 mb-2 text-xs font-medium tracking-wider text-muted-foreground uppercase">
              {section.title}
            </div>
            <div className="space-y-1">
              {section.items.map((item) => (
                <NavLink
                  key={item.path}
                  to={item.path}
                  className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent"
                  activeClassName="bg-primary/10 text-primary font-medium"
                  onClick={() => setIsMobileOpen(false)}
                >
                  <item.icon className="h-5 w-5" />
                  {item.label}
                </NavLink>
              ))}
            </div>
          </div>
        ))}
      </nav>

      <div className="p-4 border-t border-border space-y-1">
        <button className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent">
          <HelpCircle className="h-5 w-5" />
          {t('help')}
        </button>
        <button
          onClick={handleLogout}
          className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent"
        >
          <LogOut className="h-5 w-5" />
          {t('logout')}
        </button>
      </div>
    </aside>

    {/* Sidebar mobile como drawer */}
    <div className={`${isMobileOpen ? 'block' : 'hidden'} md:hidden fixed inset-0 z-40`}>
      <div className="absolute inset-0 bg-black/40" onClick={() => setIsMobileOpen(false)} />
      <aside className={`absolute inset-y-0 left-0 w-64 bg-card border-r border-border z-50 flex flex-col`}>
        <div className="p-6 border-b border-border flex items-center justify-between">
          <div className="flex items-center gap-2">
            <div className="w-10 h-10 rounded-xl bg-primary/10 flex items-center justify-center">
              <Construction className="h-6 w-6 text-primary" />
            </div>
            <div className="flex-1">
              <h2 className="font-semibold text-sm">{`${t('app_name')} ${t('app_tagline')}`}</h2>
              <p className="text-xs text-muted-foreground">{subscriptionType || '-'}</p>
            </div>
          </div>
          <button className="p-2 hover:bg-accent rounded-lg" onClick={() => setIsMobileOpen(false)} aria-label="Fechar menu">
            <X className="h-5 w-5" />
          </button>
        </div>
        <nav className="flex-1 p-4 space-y-6 overflow-y-auto">
          {sections.map((section) => (
            <div key={section.title}>
              <div className="px-4 mb-2 text-xs font-medium tracking-wider text-muted-foreground uppercase">
                {section.title}
              </div>
              <div className="space-y-1">
                {section.items.map((item) => (
                  <NavLink
                    key={item.path}
                    to={item.path}
                    className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent"
                    activeClassName="bg-primary/10 text-primary font-medium"
                    onClick={() => setIsMobileOpen(false)}
                  >
                    <item.icon className="h-5 w-5" />
                    {item.label}
                  </NavLink>
                ))}
              </div>
            </div>
          ))}
        </nav>
        <div className="p-4 border-t border-border space-y-1">
          <button className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent">
            <HelpCircle className="h-5 w-5" />
            {t('help')}
          </button>
          <button
            onClick={() => { setIsMobileOpen(false); handleLogout(); }}
            className="w-full flex items-center gap-3 px-4 py-3 rounded-lg text-foreground hover:bg-accent"
          >
            <LogOut className="h-5 w-5" />
            {t('logout')}
          </button>
        </div>
      </aside>
    </div>
    </>
  );
};
