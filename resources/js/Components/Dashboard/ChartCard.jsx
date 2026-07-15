import React from 'react';
import { Line } from 'react-chartjs-2';
import { Chart, CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend } from 'chart.js';

Chart.register(CategoryScale, LinearScale, PointElement, LineElement, Tooltip, Legend);

export default function ChartCard({ title, data, options }) {
  return (
    <div className="card">
      <div className="card-body">
        <h6 className="card-title">{title}</h6>
        {data ? <Line data={data} options={options} /> : <div className="text-muted">No data</div>}
      </div>
    </div>
  );
}
