//dashboard - pagina inicial do sistema (obras)
import { useState, useEffect, useMemo } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Progress } from '@/components/ui/progress';
import { Sidebar } from '@/components/Sidebar';
import { Header } from '@/components/Header';
import { Plus } from 'lucide-react';
import { toast } from 'sonner';

interface ProjectCounters {
  reports: number;
  reminders: number;
  photos: number;
  videos: number;
}

interface Project {
  id: string;
  name: string;
  image: string;
  startDate: string;
  endDate: string;
  progress: number;
  status: 'in_progress' | 'completed' | 'delayed' | 'canceled' | 'planning';
  statusText: string; // texto vindo/mapeado do backend
  counters?: ProjectCounters;
}

const Dashboard = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [filter, setFilter] = useState<'all' | 'in_progress' | 'completed' | 'delayed'>('all');
  const [searchTerm, setSearchTerm] = useState('');
  const [projects, setProjects] = useState<Project[]>([]);
  const [isLoading, setIsLoading] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    const isAuthenticated = localStorage.getItem('isAuthenticated');
    if (!isAuthenticated) {
      navigate('/login');
      return;
    }

    // Buscar obras na API
    setIsLoading(true);
    setError(null);
    const token = localStorage.getItem('userToken');
    const userId = localStorage.getItem('userId');
    const controller = new AbortController();

    const qs = userId ? `?usuario_id=${encodeURIComponent(userId)}` : '';
    fetch(`/appfacil/obras.php${qs}`, {
      method: 'GET',
      headers: token ? { 'Authorization': `Bearer ${token}` } : {},
      signal: controller.signal,
    })
      .then(async (response) => {
        if (!response.ok) {
          const text = await response.text().catch(() => '');
          throw new Error(`Falha HTTP ${response.status}. ${text || ''}`);
        }
        return response.json();
      })
      .then((data) => {
        let list: any[] = [];
        if (Array.isArray(data)) {
          list = data;
        } else if (Array.isArray((data as any)?.data)) {
          list = (data as any).data;
        } else if (Array.isArray((data as any)?.obras)) {
          list = (data as any).obras;
        }
        setProjects(list.map((o, i) => normalizeProject(o, i)));
      })
      .catch((error) => {
        console.error('Erro ao carregar obras:', error);
        toast.error('Não foi possível carregar as obras do servidor.');
        setError('Falha ao carregar obras. Exibindo dados de exemplo.');
      })
      .finally(() => setIsLoading(false));

    return () => controller.abort();
  }, [navigate]);

  // Fallback de projetos para quando API não responder
  const fallbackProjects: Project[] = useMemo(() => ([
    {
      id: '1',
      name: 'Residencial Jardins',
      image: 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=80',
      startDate: '01/03/2024',
      endDate: '15/09/2024',
      progress: 75,
      status: 'in_progress',
    },
    {
      id: '2',
      name: 'Edifício Comercial Alpha',
      image: 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?w=800&q=80',
      startDate: '10/01/2023',
      endDate: '20/02/2024',
      progress: 100,
      status: 'completed',
    },
    {
      id: '3',
      name: 'Shopping Center Plaza',
      image: 'https://images.unsplash.com/photo-1497366216548-37526070297c?w=800&q=80',
      startDate: '18/08/2023',
      endDate: '30/06/2024',
      progress: 45,
      status: 'delayed',
    },
  ]), []);

  // Normaliza objeto vindo da API para Project
  const normalizeProject = (obra: any, index: number): Project => {
    const rawStatus = String(obra?.status ?? obra?.situacao ?? obra?.state ?? 'em_andamento');

    const mapStatusCategory = (s: string): Project['status'] => {
      const v = s.toLowerCase();
      if (['completed', 'concluida', 'concluído', 'concluido', 'finalizada', 'finalizado'].includes(v)) return 'completed';
      if (['canceled', 'cancelado', 'cancelada', 'cancelar', 'cancelada', 'cancelado'].includes(v)) return 'canceled';
      if (['planning', 'planejamento', 'em_planejamento', 'em planejamento'].includes(v)) return 'planning';
      if (['delayed', 'atrasada', 'atrasado', 'vencida', 'vencido'].includes(v)) return 'delayed';
      return 'in_progress';
    };

    const mapStatusText = (s: string): string => {
      const v = s.toLowerCase();
      if (['em_andamento', 'andamento', 'in_progress'].includes(v)) return t('in_progress');
      if (['concluida', 'concluído', 'concluido', 'finalizada', 'completed'].includes(v)) return t('completed');
      if (['cancelado', 'cancelada', 'canceled'].includes(v)) return 'Cancelado';
      if (['planning', 'planejamento', 'em_planejamento', 'em planejamento'].includes(v)) return 'Planejamento';
      if (['atrasada', 'atrasado', 'delayed'].includes(v)) return t('delayed');
      // Fallback para mostrar exatamente o que veio do backend
      return obra?.status ?? obra?.situacao ?? s;
    };

    return {
      id: String(obra?.id ?? obra?.obra_id ?? index + 1),
      name: obra?.name ?? obra?.nome ?? obra?.titulo ?? `Obra ${index + 1}`,
      image: obra?.image ?? obra?.imagem ?? obra?.foto ?? 'https://images.unsplash.com/photo-1503387762-592deb58ef4e?w=800&q=80',
      startDate: obra?.startDate ?? obra?.data_inicio ?? obra?.inicio ?? '',
      endDate: obra?.endDate ?? obra?.previsao ?? obra?.data_fim ?? '',
      progress: Number(obra?.progress ?? obra?.progresso ?? 0) || 0,
      status: mapStatusCategory(rawStatus),
      statusText: mapStatusText(rawStatus),
    };
  };

  const listForView = (projects.length ? projects : fallbackProjects);
  const filteredByStatus = (filter === 'all') ? listForView : listForView.filter(p => p.status === filter);
  const filteredProjects = filteredByStatus.filter(p => p.name.toLowerCase().includes(searchTerm.toLowerCase()));

  const getStatusBadge = (status: Project['status'], label: string) => {
    const variants = {
      in_progress: { variant: 'default' as const, className: 'bg-blue-600 text-white' },
      completed: { variant: 'default' as const, className: 'bg-green-600 text-white' },
      delayed: { variant: 'default' as const, className: 'bg-red-600 text-white' },
      canceled: { variant: 'default' as const, className: 'bg-red-600 text-white' },
      planning: { variant: 'default' as const, className: 'bg-purple-600 text-white' },
    } as const;

    const config = variants[status] ?? variants.in_progress;
    return (
      <Badge variant={config.variant} className={config.className}>
        {label}
      </Badge>
    );
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <div className="flex-1 flex flex-col">
        <Header />
        
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Page Header */}
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">{t('general_view')}</h1>
              <Button className="gap-2">
                <Plus className="h-4 w-4" />
                {t('add_new_construction')}
              </Button>
            </div>

            {/* Search and Filters */}
            <div className="flex flex-col md:flex-row md:items-center gap-3">
              <input
                type="text"
                value={searchTerm}
                onChange={(e) => setSearchTerm(e.target.value)}
                placeholder={t('search_by_name')}
                className="w-full md:w-80 px-3 py-2 rounded-md border border-border bg-background text-foreground"
              />
              <div className="flex gap-2">
              <Button
                variant={filter === 'all' ? 'default' : 'outline'}
                onClick={() => setFilter('all')}
                className={filter === 'all' ? 'bg-primary/10 text-primary hover:bg-primary/20' : ''}
              >
                {t('all')}
              </Button>
              <Button
                variant={filter === 'in_progress' ? 'default' : 'outline'}
                onClick={() => setFilter('in_progress')}
                className={filter === 'in_progress' ? 'bg-warning/10 text-warning hover:bg-warning/20' : ''}
              >
                {t('in_progress')}
              </Button>
              <Button
                variant={filter === 'completed' ? 'default' : 'outline'}
                onClick={() => setFilter('completed')}
                className={filter === 'completed' ? 'bg-success/10 text-success hover:bg-success/20' : ''}
              >
                {t('completed')}
              </Button>
              <Button
                variant={filter === 'delayed' ? 'default' : 'outline'}
                onClick={() => setFilter('delayed')}
                className={filter === 'delayed' ? 'bg-destructive/10 text-destructive hover:bg-destructive/20' : ''}
              >
                {t('delayed')}
              </Button>
              </div>
            </div>

            {/* Projects Grid */}
            {isLoading && (
              <div className="text-sm text-muted-foreground">Carregando obras...</div>
            )}
            {error && (
              <div className="text-sm text-destructive">{error}</div>
            )}
            <div className="grid gap-6 md:grid-cols-2 lg:grid-cols-3">
              {filteredProjects.map((project) => (
                <div key={project.id} className="bg-card rounded-xl border border-border overflow-hidden hover:shadow-lg transition-shadow">
                  <div className="aspect-video relative">
                    <img
                      src={project.image}
                      alt={project.name}
                      className="w-full h-full object-cover"
                    />
                    <div className="absolute top-4 right-4">
                      {getStatusBadge(project.status, project.statusText)}
                    </div>
                  </div>
                  
                  <div className="p-5 space-y-4">
                    <h3 className="font-semibold text-lg">{project.name}</h3>
                    
                    <div className="space-y-2 text-sm text-muted-foreground">
                      <p>{t('start_date')} {project.startDate}</p>
                      <p>{t('estimated_time')} {project.endDate}</p>
                    </div>

                    <div className="space-y-2">
                      <div className="flex justify-between text-sm">
                        <span className="text-muted-foreground">{t('progress')}</span>
                        <span className="font-semibold">{project.progress}%</span>
                      </div>
                      <Progress value={project.progress} className="h-2" />
                    </div>

                    {/* Counters */}
                    <div className="grid grid-cols-4 gap-3">
                      <div className="rounded-lg bg-muted p-3 text-center">
                        <div className="text-orange-600 font-semibold text-lg">{project.counters?.reports ?? 0}</div>
                        <div className="text-xs uppercase tracking-wide">{t('reports')}</div>
                      </div>
                      <div className="rounded-lg bg-muted p-3 text-center">
                        <div className="text-orange-600 font-semibold text-lg">{project.counters?.reminders ?? 0}</div>
                        <div className="text-xs uppercase tracking-wide">{t('reminders')}</div>
                      </div>
                      <div className="rounded-lg bg-muted p-3 text-center">
                        <div className="text-orange-600 font-semibold text-lg">{project.counters?.photos ?? 0}</div>
                        <div className="text-xs uppercase tracking-wide">{t('photos')}</div>
                      </div>
                      <div className="rounded-lg bg-muted p-3 text-center">
                        <div className="text-orange-600 font-semibold text-lg">{project.counters?.videos ?? 0}</div>
                        <div className="text-xs uppercase tracking-wide">{t('videos')}</div>
                      </div>
                    </div>

                    <Button 
                      variant="outline" 
                      className="w-full"
                    >
                      {t('view_details')}
                    </Button>
                  </div>
                </div>
              ))}
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Dashboard;
