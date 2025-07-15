// Chart.js 图表管理
import Chart from 'chart.js/auto';

// 默认配置
const defaultOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        }
    },
    scales: {
        x: {
            display: true,
            grid: {
                display: false
            }
        },
        y: {
            display: true,
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            }
        }
    }
};

// 颜色配置
const colors = {
    primary: 'rgb(59, 130, 246)',
    success: 'rgb(16, 185, 129)',
    warning: 'rgb(245, 158, 11)',
    danger: 'rgb(239, 68, 68)',
    purple: 'rgb(147, 51, 234)',
    orange: 'rgb(249, 115, 22)',
    gradient: {
        blue: ['rgba(59, 130, 246, 0.8)', 'rgba(59, 130, 246, 0.2)'],
        green: ['rgba(16, 185, 129, 0.8)', 'rgba(16, 185, 129, 0.2)'],
        purple: ['rgba(147, 51, 234, 0.8)', 'rgba(147, 51, 234, 0.2)'],
        orange: ['rgba(249, 115, 22, 0.8)', 'rgba(249, 115, 22, 0.2)']
    }
};

// 创建渐变色
function createGradient(ctx, color1, color2) {
    const gradient = ctx.createLinearGradient(0, 0, 0, 400);
    gradient.addColorStop(0, color1);
    gradient.addColorStop(1, color2);
    return gradient;
}

// 销售趋势图表
export function createSalesTrendChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [{
                label: '销售额',
                data: data.sales,
                borderColor: colors.primary,
                backgroundColor: createGradient(ctx, ...colors.gradient.blue),
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.primary,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }, {
                label: '利润',
                data: data.profits,
                borderColor: colors.success,
                backgroundColor: createGradient(ctx, ...colors.gradient.green),
                borderWidth: 3,
                fill: true,
                tension: 0.4,
                pointBackgroundColor: colors.success,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 5
            }]
        },
        options: {
            ...defaultOptions,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                ...defaultOptions.plugins,
                title: {
                    display: true,
                    text: '销售趋势分析'
                }
            }
        }
    });
}

// 库存分布饼图
export function createInventoryPieChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [{
                data: data.values,
                backgroundColor: [
                    colors.primary,
                    colors.success,
                    colors.warning,
                    colors.danger,
                    colors.purple,
                    colors.orange
                ],
                borderWidth: 0,
                hoverBorderWidth: 2,
                hoverBorderColor: '#fff'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '60%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 20,
                        usePointStyle: true
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.parsed;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = ((value / total) * 100).toFixed(1);
                            return `${label}: ${value} (${percentage}%)`;
                        }
                    }
                }
            }
        }
    });
}

// 退货原因柱状图
export function createReturnReasonChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: '退货数量',
                data: data.values,
                backgroundColor: [
                    colors.danger,
                    colors.warning,
                    colors.orange,
                    colors.primary,
                    colors.purple
                ],
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaultOptions,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        title: function() {
                            return '';
                        },
                        label: function(context) {
                            return `${context.label}: ${context.parsed.y}件`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
}

// 盘点频率图表
export function createInventoryCheckChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: '盘点次数',
                data: data.values,
                backgroundColor: colors.purple,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaultOptions,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    },
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
}

// 多仓库对比图表
export function createStoreComparisonChart(canvas, data) {
    const ctx = canvas.getContext('2d');
    
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: data.labels,
            datasets: [{
                label: '销售额',
                data: data.sales,
                backgroundColor: colors.primary,
                borderRadius: 8,
                borderSkipped: false,
            }, {
                label: '利润',
                data: data.profits,
                backgroundColor: colors.success,
                borderRadius: 8,
                borderSkipped: false,
            }]
        },
        options: {
            ...defaultOptions,
            scales: {
                x: {
                    grid: {
                        display: false
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                }
            }
        }
    });
}

// 实时数据更新函数
export function updateChart(chart, newData) {
    chart.data.labels = newData.labels;
    chart.data.datasets.forEach((dataset, index) => {
        if (newData.datasets && newData.datasets[index]) {
            dataset.data = newData.datasets[index].data;
        }
    });
    chart.update('active');
}

// 图表响应式调整
export function makeChartResponsive(chart) {
    function handleResize() {
        chart.resize();
    }
    
    window.addEventListener('resize', handleResize);
    
    return function cleanup() {
        window.removeEventListener('resize', handleResize);
    };
}

// 获取数据的辅助函数
async function fetchChartData(endpoint) {
    try {
        const response = await fetch(`/api/charts/${endpoint}`);
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return await response.json();
    } catch (error) {
        console.error(`Failed to fetch ${endpoint} data:`, error);
        return null;
    }
}

// 初始化所有图表
export async function initializeCharts() {
    // 销售趋势图表
    const salesTrendCanvas = document.getElementById('salesTrendChart');
    if (salesTrendCanvas) {
        const salesData = await fetchChartData('sales-trend');
        if (salesData) {
            window.salesTrendChart = createSalesTrendChart(salesTrendCanvas, salesData);
            makeChartResponsive(window.salesTrendChart);
        } else {
            // 降级数据
            const fallbackData = {
                labels: ['1月', '2月', '3月', '4月', '5月', '6月'],
                sales: [0, 0, 0, 0, 0, 0],
                profits: [0, 0, 0, 0, 0, 0]
            };
            window.salesTrendChart = createSalesTrendChart(salesTrendCanvas, fallbackData);
            makeChartResponsive(window.salesTrendChart);
        }
    }
    
    // 库存分布图表
    const inventoryPieCanvas = document.getElementById('inventoryPieChart');
    if (inventoryPieCanvas) {
        const inventoryData = await fetchChartData('inventory-distribution');
        if (inventoryData) {
            window.inventoryPieChart = createInventoryPieChart(inventoryPieCanvas, inventoryData);
            makeChartResponsive(window.inventoryPieChart);
        } else {
            // 降级数据
            const fallbackData = {
                labels: ['暂无库存数据'],
                values: [0]
            };
            window.inventoryPieChart = createInventoryPieChart(inventoryPieCanvas, fallbackData);
            makeChartResponsive(window.inventoryPieChart);
        }
    }
    
    // 退货原因图表
    const returnReasonCanvas = document.getElementById('returnReasonChart');
    if (returnReasonCanvas) {
        const returnData = await fetchChartData('return-reasons');
        if (returnData) {
            window.returnReasonChart = createReturnReasonChart(returnReasonCanvas, returnData);
            makeChartResponsive(window.returnReasonChart);
        } else {
            // 降级数据
            const fallbackData = {
                labels: ['暂无退货数据'],
                values: [0]
            };
            window.returnReasonChart = createReturnReasonChart(returnReasonCanvas, fallbackData);
            makeChartResponsive(window.returnReasonChart);
        }
    }
    
    // 盘点频率图表
    const inventoryCheckCanvas = document.getElementById('inventoryCheckChart');
    if (inventoryCheckCanvas) {
        const checkData = await fetchChartData('inventory-check-frequency');
        if (checkData) {
            window.inventoryCheckChart = createInventoryCheckChart(inventoryCheckCanvas, checkData);
            makeChartResponsive(window.inventoryCheckChart);
        } else {
            // 降级数据
            const fallbackData = {
                labels: ['第1周', '第2周', '第3周', '第4周'],
                values: [0, 0, 0, 0]
            };
            window.inventoryCheckChart = createInventoryCheckChart(inventoryCheckCanvas, fallbackData);
            makeChartResponsive(window.inventoryCheckChart);
        }
    }
    
    // 多仓库对比图表
    const storeComparisonCanvas = document.getElementById('storeComparisonChart');
    if (storeComparisonCanvas) {
        const storeData = await fetchChartData('store-comparison');
        if (storeData) {
            window.storeComparisonChart = createStoreComparisonChart(storeComparisonCanvas, storeData);
            makeChartResponsive(window.storeComparisonChart);
        } else {
            // 降级数据
            const fallbackData = {
                labels: ['暂无仓库数据'],
                sales: [0],
                profits: [0]
            };
            window.storeComparisonChart = createStoreComparisonChart(storeComparisonCanvas, fallbackData);
            makeChartResponsive(window.storeComparisonChart);
        }
    }
}

// 刷新图表数据
export async function refreshChartsData() {
    // 刷新销售趋势图表
    if (window.salesTrendChart) {
        const salesData = await fetchChartData('sales-trend');
        if (salesData) {
            updateChart(window.salesTrendChart, {
                labels: salesData.labels,
                datasets: [
                    { data: salesData.sales },
                    { data: salesData.profits }
                ]
            });
        }
    }

    // 刷新库存分布图表
    if (window.inventoryPieChart) {
        const inventoryData = await fetchChartData('inventory-distribution');
        if (inventoryData) {
            updateChart(window.inventoryPieChart, {
                labels: inventoryData.labels,
                datasets: [{ data: inventoryData.values }]
            });
        }
    }

    // 刷新退货原因图表
    if (window.returnReasonChart) {
        const returnData = await fetchChartData('return-reasons');
        if (returnData) {
            updateChart(window.returnReasonChart, {
                labels: returnData.labels,
                datasets: [{ data: returnData.values }]
            });
        }
    }

    // 刷新盘点频率图表
    if (window.inventoryCheckChart) {
        const checkData = await fetchChartData('inventory-check-frequency');
        if (checkData) {
            updateChart(window.inventoryCheckChart, {
                labels: checkData.labels,
                datasets: [{ data: checkData.values }]
            });
        }
    }

    // 刷新多仓库对比图表
    if (window.storeComparisonChart) {
        const storeData = await fetchChartData('store-comparison');
        if (storeData) {
            updateChart(window.storeComparisonChart, {
                labels: storeData.labels,
                datasets: [
                    { data: storeData.sales },
                    { data: storeData.profits }
                ]
            });
        }
    }
}

// DOM加载完成后初始化图表
document.addEventListener('DOMContentLoaded', () => {
    initializeCharts();
}); 