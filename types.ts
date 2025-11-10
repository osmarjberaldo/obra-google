
export enum WorkStatus {
  InProgress = 'InProgress',
  Completed = 'Completed',
  Delayed = 'Delayed',
}

export interface WorkProject {
  id: number;
  name: string;
  startDate: string;
  endDate: string;
  progress: number;
  status: WorkStatus;
  imageUrl: string;
}

export type NavItemKey = 'dashboard' | 'works' | 'reports' | 'team' | 'settings';
   