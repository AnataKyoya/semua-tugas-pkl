<?php

namespace App\Controllers;

use App\Models\ArtikelModel;
use App\Models\MLModel;

class Home extends BaseController
{
    public function index()
    {
        $sessionId = session_id();

        $modelML = new MLModel();
        $data = $modelML->where('session_id', $sessionId)->select(['result', 'tracking'])->first();

        $artikelModel = new ArtikelModel();
        $dataArtikel = $artikelModel->findAll();

        if (!$data) {
            $modelML->insert([
                'session_id' => $sessionId
            ]);
        } else {
            $dataScore = [];
            $trackData = json_decode($data['tracking'], true) ?? [];

            if (count($trackData) > 0) {
                foreach ($trackData as $i => $t) {
                    $kategori = $t['kategori'];
                    $count = $t['data']['count'];
                    $time = $t['data']['time'] ?? 0;
                    $scroll = $t['data']['scroll'] ?? 0;

                    $rataRataWaktu = $time / $count;
                    $rataRataScroll = $scroll / $count;

                    $dataScore[$kategori] = round(($dataScore[$kategori] ?? 0) + log($count + 1), 1);

                    if ($rataRataWaktu > 18) {
                        $dataScore[$kategori] = ($dataScore[$kategori] ?? 0) + 3;
                    } else {
                        $dataScore[$kategori] = ($dataScore[$kategori] ?? 0) + 1;
                    }

                    if ($rataRataScroll > 18) {
                        $dataScore[$kategori] = ($dataScore[$kategori] ?? 0) + 3;
                    }
                }

                arsort($dataScore);
                $bestLabel = array_slice($dataScore, 0, 2, true);
                $labels = array_keys($bestLabel);

                dd($labels);

                $dataRekomendasi = $artikelModel->whereIn('kategori', $labels)->findAll(10);
            }
        }

        return view('beranda', [
            'hasil' => $dataRekomendasi ?? "none",
            'artikel' => $dataArtikel
        ]);
    }

    public function artikel($id)
    {
        $artikelModel = new ArtikelModel();
        $data = $artikelModel->find($id);

        return view('list_artikel', [
            'artikel' => $data
        ]);
    }

    public function aktivitas()
    {
        $sessionId = session_id();
        $data = $this->request->getJSON(true);

        // return $this->response->setJSON($data['type']);
        // return $this->response->setJSON($intTime);

        $modelML = new MLModel();

        $row = $modelML
            ->where('session_id', $sessionId)
            ->select(['id', 'tracking'])
            ->first();

        $trackData = json_decode($row['tracking'], true) ?? [];

        $found = false;

        $intTime = !$data['count'] ? ($data['type'] === 'time_spent_seconds' ? (int) $data['value'] : 0) : 0;
        $intScroll = !$data['count'] ? ($data['type'] === 'scroll_percentage' ? (int) $data['value'] : 0) : 0;

        foreach ($trackData as &$item) {
            if ($item['kategori'] === $data['article_kategori']) {

                if (!empty($data['count'])) {
                    $item['data']['count'] = ($item['data']['count'] ?? 0) + 1;
                }

                $item['data']['time'] = ($item['data']['time'] ?? 0) + $intTime;
                $item['data']['scroll'] = ($item['data']['scroll'] ?? 0) + $intScroll;

                $found = true;
                break;
            }
        }
        unset($item); // WAJIB setelah foreach reference

        if (!$found) {
            $trackData[] = [
                'kategori' => $data['article_kategori'],
                'data' => [
                    'count' => 1
                ]
            ];
        }

        $modelML->update($row['id'], [
            'tracking' => json_encode($trackData)
        ]);


        return $this->response->setJSON(['status' => 'ok']);
    }
}
