import pandas as pd
import numpy as np
import matplotlib.pyplot as plt
from scipy.stats import norm

df = pd.read_csv('/content/pontianak_weather_daily_2021_2024.csv')

suhu_harian = df['TAVG'].dropna()

mu_asli = suhu_harian.mean()
sigma_asli = suhu_harian.std()

plt.figure(figsize=(9, 6))

plt.hist(suhu_harian, bins=30, density=True, alpha=0.5, color='teal', edgecolor='black', label='Suhu Udara Riil (Empiris)')

xmin, xmax = plt.xlim()
x = np.linspace(xmin, xmax, 100)

p = norm.pdf(x, mu_asli, sigma_asli)

plt.plot(x, p, 'r', linewidth=2.5, label=f'PDF Normal (mu={mu_asli:.2f}, sigma={sigma_asli:.2f})')

plt.title('Distribusi Suhu Udara Harian Pontianak (2021-2024)', fontsize=14, fontweight='bold')
plt.xlabel('Suhu Udara (°C)', fontsize=12)
plt.ylabel('Kepadatan Kuantitas (Density)', fontsize=12)
plt.legend(loc='upper right') 
plt.grid(axis='both', alpha=0.3, linestyle='--')

plt.show()