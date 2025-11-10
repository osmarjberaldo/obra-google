import { useState } from 'react';
import { useNavigate } from 'react-router-dom';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Badge } from '@/components/ui/badge';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  Table,
  TableBody,
  TableCell,
  TableHead,
  TableHeader,
  TableRow,
} from '@/components/ui/table';
import { Sidebar } from '@/components/Sidebar';
import { Header } from '@/components/Header';
import { Search, Plus, Filter, Calendar, Download, MoreVertical } from 'lucide-react';

interface Report {
  id: string;
  projectName: string;
  date: string;
  status: 'completed' | 'in_progress' | 'delayed';
  responsible: string;
}

const Reports = () => {
  const { t } = useTranslation();
  const navigate = useNavigate();
  const [searchQuery, setSearchQuery] = useState('');
  const [selectedProject, setSelectedProject] = useState('all');
  const [selectedPeriod, setSelectedPeriod] = useState('30');
  const [selectedStatus, setSelectedStatus] = useState('all');

  const reports: Report[] = [
    {
      id: '1',
      projectName: 'Edifício Infinity Tower',
      date: '25/10/2023',
      status: 'completed',
      responsible: 'Carlos Silva',
    },
    {
      id: '2',
      projectName: 'Residencial Park Avenue',
      date: '25/10/2023',
      status: 'in_progress',
      responsible: 'Ana Pereira',
    },
    {
      id: '3',
      projectName: 'Centro Comercial Metrópole',
      date: '24/10/2023',
      status: 'delayed',
      responsible: 'João Martins',
    },
    {
      id: '4',
      projectName: 'Hospital São Lucas',
      date: '24/10/2023',
      status: 'completed',
      responsible: 'Mariana Costa',
    },
  ];

  const getStatusBadge = (status: Report['status']) => {
    const variants = {
      in_progress: { label: t('in_progress'), className: 'bg-warning text-warning-foreground' },
      completed: { label: t('completed'), className: 'bg-success text-success-foreground' },
      delayed: { label: t('delayed'), className: 'bg-destructive text-destructive-foreground' },
    };
    
    const config = variants[status];
    return (
      <Badge className={config.className}>
        {config.label}
      </Badge>
    );
  };

  const filteredReports = reports.filter(report => {
    const matchesSearch = report.projectName.toLowerCase().includes(searchQuery.toLowerCase());
    const matchesStatus = selectedStatus === 'all' || report.status === selectedStatus;
    return matchesSearch && matchesStatus;
  });

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <div className="flex-1 flex flex-col">
        <Header />
        
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
              <div>
                <h1 className="text-3xl font-bold text-foreground">Relatórios Diários de Obra</h1>
                <p className="text-muted-foreground mt-1">Acompanhe o progresso diário de todas as suas obras.</p>
              </div>
              <Button className="gap-2">
                <Plus className="h-4 w-4" />
                Novo Relatório
              </Button>
            </div>

            {/* Filters */}
            <div className="flex gap-4 items-center flex-wrap">
              <div className="relative flex-1 min-w-[300px]">
                <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" />
                <Input
                  placeholder="Pesquisar por obra ou data..."
                  value={searchQuery}
                  onChange={(e) => setSearchQuery(e.target.value)}
                  className="pl-9"
                />
              </div>

              <Select value={selectedProject} onValueChange={setSelectedProject}>
                <SelectTrigger className="w-[200px]">
                  <div className="flex items-center gap-2">
                    <Filter className="h-4 w-4" />
                    <SelectValue placeholder="Obra: Todas" />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Obra: Todas</SelectItem>
                  <SelectItem value="project1">Edifício Infinity Tower</SelectItem>
                  <SelectItem value="project2">Residencial Park Avenue</SelectItem>
                  <SelectItem value="project3">Centro Comercial Metrópole</SelectItem>
                </SelectContent>
              </Select>

              <Select value={selectedPeriod} onValueChange={setSelectedPeriod}>
                <SelectTrigger className="w-[200px]">
                  <div className="flex items-center gap-2">
                    <Calendar className="h-4 w-4" />
                    <SelectValue />
                  </div>
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="7">Últimos 7 dias</SelectItem>
                  <SelectItem value="30">Últimos 30 dias</SelectItem>
                  <SelectItem value="90">Últimos 90 dias</SelectItem>
                </SelectContent>
              </Select>

              <Select value={selectedStatus} onValueChange={setSelectedStatus}>
                <SelectTrigger className="w-[180px]">
                  <SelectValue />
                </SelectTrigger>
                <SelectContent>
                  <SelectItem value="all">Status: Todos</SelectItem>
                  <SelectItem value="completed">Concluído</SelectItem>
                  <SelectItem value="in_progress">Em Andamento</SelectItem>
                  <SelectItem value="delayed">Atrasado</SelectItem>
                </SelectContent>
              </Select>

              <Button variant="outline" size="icon">
                <Download className="h-4 w-4" />
              </Button>
            </div>

            {/* Table */}
            <div className="bg-card rounded-lg border border-border overflow-hidden">
              <Table>
                <TableHeader>
                  <TableRow>
                    <TableHead>NOME DA OBRA</TableHead>
                    <TableHead>DATA</TableHead>
                    <TableHead>STATUS</TableHead>
                    <TableHead>RESPONSÁVEL</TableHead>
                    <TableHead className="w-12"></TableHead>
                  </TableRow>
                </TableHeader>
                <TableBody>
                  {filteredReports.map((report) => (
                    <TableRow key={report.id} className="hover:bg-muted/50 cursor-pointer">
                      <TableCell className="font-medium">{report.projectName}</TableCell>
                      <TableCell className="text-muted-foreground">{report.date}</TableCell>
                      <TableCell>{getStatusBadge(report.status)}</TableCell>
                      <TableCell className="text-muted-foreground">{report.responsible}</TableCell>
                      <TableCell>
                        <Button variant="ghost" size="icon">
                          <MoreVertical className="h-4 w-4" />
                        </Button>
                      </TableCell>
                    </TableRow>
                  ))}
                </TableBody>
              </Table>
            </div>

            {/* Pagination */}
            <div className="flex items-center justify-between">
              <p className="text-sm text-muted-foreground">
                Mostrando 1 a 4 de 20 resultados
              </p>
              <div className="flex gap-2">
                <Button variant="outline" size="sm">
                  Anterior
                </Button>
                <Button variant="outline" size="sm">
                  Próximo
                </Button>
              </div>
            </div>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Reports;
