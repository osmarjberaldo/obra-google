
import React, { useState } from 'react';
import Sidebar from '../components/Sidebar';
import Header from '../components/Header';
import WorksOverview from './WorksOverview';
import { NavItemKey } from '../types';

interface DashboardProps {
  onLogout: () => void;
}

const Dashboard: React.FC<DashboardProps> = ({ onLogout }) => {
    const [activeNavItem, setActiveNavItem] = useState<NavItemKey>('works');

    const renderContent = () => {
        switch (activeNavItem) {
            case 'works':
                return <WorksOverview />;
            // Add cases for other pages here
            case 'dashboard':
            case 'reports':
            case 'team':
            case 'settings':
            default:
                return (
                    <div className="p-8">
                        <h1 className="text-2xl font-bold text-slate-800">Page for "{activeNavItem}"</h1>
                        <p className="text-slate-600 mt-2">This page is under construction.</p>
                    </div>
                )
        }
    }

  return (
    <div className="flex min-h-screen bg-slate-50">
      <Sidebar activeItem={activeNavItem} setActiveItem={setActiveNavItem} onLogout={onLogout} />
      <div className="flex-1 flex flex-col">
        <Header />
        <main className="flex-1 p-4 sm:p-6 lg:p-8 overflow-y-auto">
          {renderContent()}
        </main>
      </div>
    </div>
  );
};

export default Dashboard;
   