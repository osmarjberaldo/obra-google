import { useState } from 'react';
import { useTranslation } from 'react-i18next';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { Badge } from '@/components/ui/badge';
import { Sidebar } from '@/components/Sidebar';
import { Header } from '@/components/Header';
import { TrendingUp, TrendingDown, DollarSign, Plus, Download } from 'lucide-react';

interface Transaction {
  id: string;
  date: string;
  description: string;
  category: string;
  type: 'income' | 'expense';
  amount: number;
}

const Financial = () => {
  const { t } = useTranslation();
  const [selectedProject, setSelectedProject] = useState('horizon');

  const transactions: Transaction[] = [
    {
      id: '1',
      date: '25/07/2024',
      description: 'Compra de cimento Portland (50 sacos)',
      category: 'Materiais',
      type: 'expense',
      amount: 1500.00,
    },
    {
      id: '2',
      date: '24/07/2024',
      description: 'Adiantamento de pagamento - Cliente',
      category: 'Receita de Projeto',
      type: 'income',
      amount: 50000.00,
    },
    {
      id: '3',
      date: '22/07/2024',
      description: 'Pagamento de folha - Equipe de obra',
      category: 'Mão de Obra',
      type: 'expense',
      amount: 25000.00,
    },
  ];

  const formatCurrency = (value: number) => {
    return new Intl.NumberFormat('pt-BR', {
      style: 'currency',
      currency: 'BRL',
    }).format(value);
  };

  return (
    <div className="flex min-h-screen bg-background">
      <Sidebar />
      
      <div className="flex-1 flex flex-col">
        <Header />
        
        <main className="flex-1 p-8 overflow-y-auto">
          <div className="max-w-7xl mx-auto space-y-6">
            {/* Header */}
            <div className="flex items-center justify-between">
              <h1 className="text-3xl font-bold text-foreground">Painel Financeiro</h1>
              <div className="flex items-center gap-3">
                <Select value={selectedProject} onValueChange={setSelectedProject}>
                  <SelectTrigger className="w-[200px]">
                    <SelectValue />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="horizon">Edifício Horizon</SelectItem>
                    <SelectItem value="jardins">Residencial Jardins</SelectItem>
                    <SelectItem value="alpha">Edifício Comercial Alpha</SelectItem>
                  </SelectContent>
                </Select>
                <Button className="gap-2">
                  <Plus className="h-4 w-4" />
                  Adicionar Lançamento
                </Button>
              </div>
            </div>

            <p className="text-muted-foreground">
              Selecione uma obra para visualizar os detalhes financeiros.
            </p>

            {/* Financial Summary Cards */}
            <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">
                    Receita Total
                  </CardTitle>
                  <DollarSign className="h-4 w-4 text-success" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">R$ 1.200.000,00</div>
                  <div className="flex items-center gap-1 text-xs text-success mt-1">
                    <TrendingUp className="h-3 w-3" />
                    <span>+15.2%</span>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">
                    Despesas Totais
                  </CardTitle>
                  <DollarSign className="h-4 w-4 text-destructive" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">R$ 850.000,00</div>
                  <div className="flex items-center gap-1 text-xs text-destructive mt-1">
                    <TrendingUp className="h-3 w-3" />
                    <span>+14.1%</span>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">
                    Saldo Atual
                  </CardTitle>
                  <DollarSign className="h-4 w-4 text-primary" />
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">R$ 350.000,00</div>
                  <div className="flex items-center gap-1 text-xs text-destructive mt-1">
                    <TrendingDown className="h-3 w-3" />
                    <span>-2.5%</span>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader className="flex flex-row items-center justify-between pb-2">
                  <CardTitle className="text-sm font-medium text-muted-foreground">
                    Progresso do Orçamento
                  </CardTitle>
                </CardHeader>
                <CardContent>
                  <div className="text-2xl font-bold">75%</div>
                  <div className="w-full bg-muted rounded-full h-2 mt-2">
                    <div className="bg-primary h-2 rounded-full" style={{ width: '75%' }}></div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Charts Section */}
            <div className="grid gap-6 md:grid-cols-2">
              <Card>
                <CardHeader>
                  <CardTitle>Fluxo de Caixa Mensal</CardTitle>
                  <p className="text-sm text-muted-foreground">Últimos 6 meses</p>
                </CardHeader>
                <CardContent>
                  <div className="h-[300px] flex items-center justify-center bg-muted/20 rounded-lg">
                    <div className="text-center text-muted-foreground">
                      <TrendingUp className="h-12 w-12 mx-auto mb-2 opacity-50" />
                      <p>Gráfico de Fluxo de Caixa</p>
                    </div>
                  </div>
                </CardContent>
              </Card>

              <Card>
                <CardHeader>
                  <CardTitle>Distribuição de Despesas</CardTitle>
                  <p className="text-sm text-muted-foreground">Por categoria</p>
                </CardHeader>
                <CardContent>
                  <div className="h-[300px] flex items-center justify-center bg-muted/20 rounded-lg">
                    <div className="text-center text-muted-foreground">
                      <DollarSign className="h-12 w-12 mx-auto mb-2 opacity-50" />
                      <p>Gráfico de Distribuição</p>
                    </div>
                  </div>
                </CardContent>
              </Card>
            </div>

            {/* Recent Transactions */}
            <Card>
              <CardHeader>
                <div className="flex items-center justify-between">
                  <div>
                    <CardTitle>Lançamentos Recentes</CardTitle>
                  </div>
                  <Button variant="outline" size="sm" className="gap-2">
                    <Download className="h-4 w-4" />
                    Exportar Relatório
                  </Button>
                </div>
              </CardHeader>
              <CardContent>
                <Table>
                  <TableHeader>
                    <TableRow>
                      <TableHead>DATA</TableHead>
                      <TableHead>DESCRIÇÃO</TableHead>
                      <TableHead>CATEGORIA</TableHead>
                      <TableHead>TIPO</TableHead>
                      <TableHead className="text-right">VALOR</TableHead>
                    </TableRow>
                  </TableHeader>
                  <TableBody>
                    {transactions.map((transaction) => (
                      <TableRow key={transaction.id}>
                        <TableCell className="text-muted-foreground">{transaction.date}</TableCell>
                        <TableCell className="font-medium">{transaction.description}</TableCell>
                        <TableCell className="text-muted-foreground">{transaction.category}</TableCell>
                        <TableCell>
                          <Badge className={transaction.type === 'income' ? 'bg-success text-success-foreground' : 'bg-destructive text-destructive-foreground'}>
                            {transaction.type === 'income' ? 'Entrada' : 'Saída'}
                          </Badge>
                        </TableCell>
                        <TableCell className={`text-right font-semibold ${transaction.type === 'income' ? 'text-success' : 'text-destructive'}`}>
                          {transaction.type === 'income' ? '+' : '-'} {formatCurrency(transaction.amount)}
                        </TableCell>
                      </TableRow>
                    ))}
                  </TableBody>
                </Table>
              </CardContent>
            </Card>
          </div>
        </main>
      </div>
    </div>
  );
};

export default Financial;
