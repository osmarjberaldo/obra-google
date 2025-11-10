import { Input } from '@/components/ui/input';
import { LanguageSelector } from '@/components/LanguageSelector';
import { Bell, User, Menu } from 'lucide-react';
import { useTranslation } from 'react-i18next';
import { useEffect, useMemo, useState } from 'react';
import { useNavigate } from 'react-router-dom';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

export const Header = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();

  type ReminderItem = {
    id: number | string;
    titulo: string;
    data_lembrete: string;
    status: string;
    obra_nome?: string;
  };

  const [reminders, setReminders] = useState<ReminderItem[]>([]);
  const [loadingReminders, setLoadingReminders] = useState(false);

  const startOfToday = useMemo(() => {
    const d = new Date();
    d.setHours(0, 0, 0, 0);
    return d;
  }, []);
  const endOfToday = useMemo(() => {
    const d = new Date();
    d.setHours(23, 59, 59, 999);
    return d;
  }, []);

  const parseDate = (raw: string): Date | null => {
    if (!raw) return null;
    // Converte "YYYY-MM-DD HH:mm:ss" para ISO inserindo 'T'
    const iso = /\d{4}-\d{2}-\d{2} \d{2}:\d{2}/.test(raw) ? raw.replace(' ', 'T') : raw;
    const d = new Date(iso);
    return isNaN(d.getTime()) ? null : d;
  };

  const isActiveReminder = (r: ReminderItem): boolean => {
    const statusActive = r.status !== 'done' && r.status !== 'canceled';
    const d = parseDate(r.data_lembrete);
    const dueToday = !!d && d >= startOfToday && d <= endOfToday;
    const overdue = !!d && d < startOfToday;
    return statusActive && (dueToday || overdue || r.status === 'overdue');
  };

  const activeReminders = useMemo(() => reminders.filter(isActiveReminder), [reminders]);
  const hasActive = activeReminders.length > 0;

  const fetchReminders = () => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    const usuario_id = localStorage.getItem('userId');
    const token = localStorage.getItem('userToken');
    if (!isAuthenticated || !usuario_id) {
      setReminders([]);
      return;
    }
    setLoadingReminders(true);
    fetch(`/appfacil/lembretes.php?usuario_id=${encodeURIComponent(usuario_id)}`, {
      method: 'GET',
      headers: token ? { Authorization: `Bearer ${token}` } : {},
    })
      .then(async (res) => {
        if (!res.ok) {
          const text = await res.text().catch(() => '');
          throw new Error(`HTTP ${res.status}. ${text || ''}`);
        }
        return res.json();
      })
      .then((json) => {
        const arr = Array.isArray(json) ? json : (json?.data || []);
        const normalized: ReminderItem[] = (arr || []).map((o: any) => ({
          id: o.id ?? o.lembrete_id ?? '',
          titulo: o.titulo ?? o.title ?? '',
          data_lembrete: o.data_lembrete ?? o.data ?? '',
          status: o.status ?? 'pending',
          obra_nome: o.obra_nome ?? o.nome_obra ?? '',
        }));
        setReminders(normalized);
      })
      .catch((err) => {
        console.error('Falha ao carregar lembretes no Header', err);
        setReminders([]);
      })
      .finally(() => setLoadingReminders(false));
  };

  useEffect(() => {
    fetchReminders();
    const interval = setInterval(fetchReminders, 60_000); // atualiza a cada 60s
    return () => clearInterval(interval);
  }, []);

  const openMobileSidebar = () => {
    const event = new CustomEvent('toggle-sidebar', { detail: 'open' });
    window.dispatchEvent(event);
  };

  return (
    <header className="h-16 border-b border-border bg-card px-4 md:px-8 flex items-center justify-between">
      <div className="flex items-center gap-2 flex-1">
        {/* Botão de menu para mobile */}
        <button className="md:hidden p-2 hover:bg-accent rounded-lg" onClick={openMobileSidebar} aria-label="Abrir menu">
          <Menu className="h-5 w-5" />
        </button>
        <div className="relative hidden md:block w-full max-w-md"></div>
      </div>

      <div className="flex items-center gap-3">
        <LanguageSelector />
        <DropdownMenu onOpenChange={(open) => open && fetchReminders()}>
          <DropdownMenuTrigger asChild>
            <button className="relative p-2 hover:bg-accent rounded-lg" aria-label="Notificações">
              <Bell className="h-5 w-5" />
              {hasActive && (
                <span className="absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full" />
              )}
            </button>
          </DropdownMenuTrigger>
          <DropdownMenuContent align="end" className="min-w-[280px]">
            <DropdownMenuLabel>{t('reminders_menu.menu_title') || 'Lembretes'}</DropdownMenuLabel>
            <DropdownMenuSeparator />
            {loadingReminders && (
              <div className="px-2 py-1 text-sm text-muted-foreground">{t('reminders_menu.menu_loading') || 'Carregando...'}</div>
            )}
            {!loadingReminders && activeReminders.length === 0 && (
              <div className="px-2 py-1 text-sm text-muted-foreground">{t('reminders_menu.menu_empty_today') || 'Sem lembretes para hoje'}</div>
            )}
            {!loadingReminders && activeReminders.map((r) => {
              const d = parseDate(r.data_lembrete);
              const dateStr = d ? `${String(d.getDate()).padStart(2,'0')}/${String(d.getMonth()+1).padStart(2,'0')}/${d.getFullYear()} ${String(d.getHours()).padStart(2,'0')}:${String(d.getMinutes()).padStart(2,'0')}` : r.data_lembrete;
              const statusColor = r.status === 'overdue' ? 'text-red-600' : r.status === 'pending' ? 'text-amber-600' : r.status === 'done' ? 'text-green-600' : 'text-muted-foreground';
              return (
                <DropdownMenuItem key={r.id} className="flex flex-col items-start gap-0.5" onClick={() => navigate(`/lembretes/ver/${r.id}`)}>
                  <span className="text-sm font-medium line-clamp-1">{r.titulo || '(Sem título)'}</span>
                  <span className="text-xs text-muted-foreground line-clamp-1">{r.obra_nome || ''}</span>
                  <span className={`text-xs ${statusColor}`}>{dateStr} · {r.status}</span>
                </DropdownMenuItem>
              );
            })}
            <DropdownMenuSeparator />
            <DropdownMenuItem onClick={() => navigate('/lembretes')} className="justify-center text-sm font-medium">
              {t('reminders_menu.menu_view_all') || 'Ver todos os lembretes'}
            </DropdownMenuItem>
          </DropdownMenuContent>
        </DropdownMenu>
        <button className="flex items-center gap-2 p-2 hover:bg-accent rounded-lg" aria-label="Perfil">
          <div className="w-8 h-8 rounded-full bg-primary/10 flex items-center justify-center">
            <User className="h-5 w-5 text-primary" />
          </div>
        </button>
      </div>
    </header>
  );
};
