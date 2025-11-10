
import React, { useState } from 'react';
import { useI18n } from '../hooks/useI18n';
import { WorkProject, WorkStatus } from '../types';

const mockWorks: WorkProject[] = [
  { id: 1, name: 'Residencial Jardins', startDate: '01/03/2023', endDate: '15/07/2024', progress: 75, status: WorkStatus.InProgress, imageUrl: 'https://picsum.photos/600/400?random=3' },
  { id: 2, name: 'Edifício Comercial Alpha', startDate: '10/01/2023', endDate: '20/02/2025', progress: 100, status: WorkStatus.Completed, imageUrl: 'https://picsum.photos/600/400?random=4' },
  { id: 3, name: 'Shopping Center Plaza', startDate: '05/06/2023', endDate: '30/08/2024', progress: 40, status: WorkStatus.Delayed, imageUrl: 'https://picsum.photos/600/400?random=5' },
  { id: 4, name: 'Hospital Municipal', startDate: '02/02/2024', endDate: '01/12/2025', progress: 15, status: WorkStatus.InProgress, imageUrl: 'https://picsum.photos/600/400?random=6' },
];

const StatusBadge: React.FC<{ status: WorkStatus }> = ({ status }) => {
    const { t } = useI18n();
    const statusMap = {
        [WorkStatus.InProgress]: { text: t('dashboard.status.in_progress'), bg: 'bg-blue-100', text_color: 'text-blue-800' },
        [WorkStatus.Completed]: { text: t('dashboard.status.completed'), bg: 'bg-green-100', text_color: 'text-green-800' },
        [WorkStatus.Delayed]: { text: t('dashboard.status.delayed'), bg: 'bg-red-100', text_color: 'text-red-800' },
    };
    const { text, bg, text_color } = statusMap[status];
    return <span className={`px-2 py-1 text-xs font-medium rounded-full ${bg} ${text_color}`}>{text}</span>;
}

const ProgressBar: React.FC<{ progress: number, status: WorkStatus }> = ({ progress, status }) => {
    const colorMap = {
        [WorkStatus.InProgress]: 'bg-orange-500',
        [WorkStatus.Completed]: 'bg-green-500',
        [WorkStatus.Delayed]: 'bg-red-500'
    };

    return (
        <div className="w-full bg-slate-200 rounded-full h-2.5">
            <div className={`${colorMap[status]} h-2.5 rounded-full`} style={{ width: `${progress}%` }}></div>
        </div>
    );
};


const WorksOverview: React.FC = () => {
    const { t } = useI18n();
    const [activeTab, setActiveTab] = useState<'all' | WorkStatus>('all');
    
    const filteredWorks = activeTab === 'all' 
        ? mockWorks 
        : mockWorks.filter(work => work.status === activeTab);

    const tabs = [
        { key: 'all', label: t('dashboard.tabs.all')},
        { key: WorkStatus.InProgress, label: t('dashboard.tabs.in_progress') },
        { key: WorkStatus.Completed, label: t('dashboard.tabs.completed') },
        { key: WorkStatus.Delayed, label: t('dashboard.tabs.delayed') },
    ];

  return (
    <div>
      <div className="flex flex-wrap items-center justify-between gap-4 mb-6">
        <h1 className="text-3xl font-bold text-slate-800">{t('dashboard.title')}</h1>
        <button className="bg-brand-orange-500 hover:bg-brand-orange-600 text-white font-bold py-2 px-4 rounded-lg flex items-center gap-2 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
            <span>{t('dashboard.add_new_work')}</span>
        </button>
      </div>

      <div className="border-b border-slate-200 mb-6">
        <nav className="-mb-px flex space-x-6">
            {tabs.map(tab => (
                 <button 
                    key={tab.key}
                    onClick={() => setActiveTab(tab.key as any)}
                    className={`whitespace-nowrap pb-4 px-1 border-b-2 font-medium text-sm transition-colors ${
                        activeTab === tab.key 
                        ? 'border-brand-orange-500 text-brand-orange-600' 
                        : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'
                    }`}
                >
                    {tab.label}
                 </button>
            ))}
        </nav>
      </div>
      
      <div className="space-y-6">
        {filteredWorks.map(work => (
            <div key={work.id} className="bg-white border border-slate-200 rounded-xl shadow-sm overflow-hidden p-5">
                <div className="md:flex md:gap-6">
                    <div className="md:w-1/3">
                        <img className="h-48 w-full object-cover rounded-lg" src={work.imageUrl} alt={work.name} />
                    </div>
                    <div className="md:w-2/3 mt-4 md:mt-0 flex flex-col">
                        <div className="flex items-start justify-between">
                            <h2 className="text-xl font-bold text-slate-800">{work.name}</h2>
                            <StatusBadge status={work.status} />
                        </div>
                        <div className="grid grid-cols-2 gap-4 text-sm text-slate-600 mt-2">
                           <div>
                                <span className="font-semibold text-slate-800">{t('dashboard.card.start')}:</span> {work.startDate}
                            </div>
                             <div>
                                <span className="font-semibold text-slate-800">{t('dashboard.card.end')}:</span> {work.endDate}
                            </div>
                        </div>
                        <div className="mt-auto pt-4">
                            <div className="flex justify-between items-center mb-2">
                                <span className="text-sm font-medium text-slate-600">{t('dashboard.card.progress')}</span>
                                <span className="text-sm font-bold text-slate-800">{work.progress}%</span>
                            </div>
                            <ProgressBar progress={work.progress} status={work.status} />
                            <button className="mt-4 w-full text-center py-2 px-4 border border-slate-300 rounded-lg text-slate-700 font-semibold hover:bg-slate-50 transition-colors">
                                {t('dashboard.card.view_details')}
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        ))}
      </div>
    </div>
  );
};

export default WorksOverview;
   