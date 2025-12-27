<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Http;

class PddiktiClient
{
    public function __construct(
        protected PddiktiTokenService $tokenService
    ) {
    }

    protected function call(string $act, array $params = [])
    {
        $token = $this->tokenService->getToken();

        $body = array_merge([
            'act' => $act,
            'token' => $token,
        ], $params);


        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post(config('pddikti.url'), $body);

        $json = $response->json();



        if (!$response->successful()) {
            throw new Exception("HTTP Error saat call $act");
        }

        if (($json['error_code'] ?? 1) !== 0) {
            throw new Exception("WS Error ($act): " . ($json['error_desc'] ?? 'Tidak diketahui'));
        }

        return $json['data'] ?? $json;
    }

    public function getProfilPT(array $filter = [])
    {
        return $this->call('GetProfilPT', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getAllPerguruanTinggi(array $filter = [])
    {
        return $this->call('GetAllPT', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getAllProdi(array $filter = [])
    {
        return $this->call('GetAllProdi', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getProdi(array $filter = [])
    {
        return $this->call('GetProdi', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getSemester(array $filter = [])
    {
        return $this->call('GetSemester', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getJalurMasuk(array $filter = [])
    {
        return $this->call('GetJalurMasuk', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getJenisPendaftaran(array $filter = [])
    {
        return $this->call('GetJenisPendaftaran', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);

    }

    public function getStatusMahasiswa(array $filter = [])
    {
        return $this->call('GetStatusMahasiswa', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getPembiayaan(array $filter = [])
    {
        return $this->call('GetPembiayaan', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListDosen(array $filter = [])
    {
        return $this->call('GetListDosen', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListPenugasanSemuaDosen(array $filter = [])
    {
        return $this->call('GetListPenugasanSemuaDosen', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getWilayah(array $filter = [])
    {
        return $this->call('GetWilayah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getAgama(array $filter = [])
    {
        return $this->call('GetAgama', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getAlatTransportasi(array $filter = [])
    {
        return $this->call('GetAlatTransportasi', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getJenisTinggal(array $filter = [])
    {
        return $this->call('GetJenisTinggal', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getPenghasilan(array $filter = [])
    {
        return $this->call('GetPenghasilan', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getJenjangPendidikan(array $filter = [])
    {
        return $this->call('GetJenjangPendidikan', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getPekerjaan(array $filter = [])
    {
        return $this->call('GetPekerjaan', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getKebutuhanKhusus(array $filter = [])
    {
        return $this->call('GetKebutuhanKhusus', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getBidangMinat(array $filter = [])
    {
        return $this->call('GetListBidangMinat', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListMatkul(array $filter = [])
    {
        return $this->call('GetListMataKuliah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getJenisEvaluasi(array $filter = [])
    {
        return $this->call('GetJenisEvaluasi', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListPenugasanDosen(array $filter = [])
    {
        return $this->call('GetListPenugasanDosen', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListSkalaNilaiProdi(array $filter = [])
    {
        return $this->call('GetListSkalaNilaiProdi', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getCountMahasiswa(array $filter = [])
    {
        return $this->call('GetCountMahasiswa', [
            'filter' => $filter['filter'] ?? '',
        ]);
    }

    public function getBiodataMahasiswa(array $filter = [])
    {
        return $this->call('GetBiodataMahasiswa', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getCountMataKuliah(array $filter = [])
    {
        return $this->call('GetCountMataKuliah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getMataKuliah(array $filter = [])
    {
        return $this->call('GetListMataKuliah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getCountRiwayatPendidikanMahasiswa(array $filter = [])
    {
        return $this->call('GetCountRiwayatPendidikanMahasiswa', [
            'filter' => $filter['filter'] ?? '',
        ]);
    }

    public function getRiwayatPendidikanMahasiswa(array $filter = [])
    {
        return $this->call('GetRiwayatPendidikanMahasiswa', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getListRiwayatPendidikanMahasiswa(array $filter = [])
    {
        return $this->call('GetListRiwayatPendidikanMahasiswa', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function insertBiodataMahasiswa(array $data)
    {
        return $this->call('InsertBiodataMahasiswa', ['record' => $data]);
    }

    public function insertRiwayatPendidikanMahasiswa(array $data)
    {
        return $this->call('InsertRiwayatPendidikanMahasiswa', ['record' => $data]);
    }


    public function insertMatkulKurikulum(array $data)
    {
        return $this->call('InsertMatkulKurikulum', ['record' => $data]);
    }

    public function insertMataKuliah(array $data)
    {
        return $this->call('InsertMataKuliah', ['record' => $data]);
    }

    public function getKurikulum(array $filter = [])
    {
        return $this->call('GetKurikulum', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getCountKurikulum(array $filter = [])
    {
        return $this->call('GetCountKurikulum', [
            'filter' => $filter['filter'] ?? '',
        ]);
    }

    public function insertKurikulum(array $data)
    {
        return $this->call('InsertKurikulum', ['record' => $data]);
    }

    public function insertKelasKuliah(array $data)
    {
        return $this->call('InsertKelasKuliah', ['record' => $data]);
    }

    public function getCountKelasKuliah(array $filter = [])
    {
        return $this->call('GetCountKelasKuliah', [
            'filter' => $filter['filter'] ?? '',
        ]);
    }

    public function getListKelasKuliah(array $filter = [])
    {
        return $this->call('GetListKelasKuliah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getDetailKelasKuliah(array $filter = [])
    {
        return $this->call('GetDetailKelasKuliah', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function insertPesertaKelasKuliah(array $data)
    {
        return $this->call('InsertPesertaKelasKuliah', ['record' => $data]);
    }

    public function insertDosenPengajarKelasKuliah(array $data)
    {
        return $this->call('InsertDosenPengajarKelasKuliah', ['record' => $data]);
    }

    public function updateNilaiPerkuliahanKelas(array $key, array $data)
    {
        return $this->call('UpdateNilaiPerkuliahanKelas', [
            'key' => $key,
            'record' => $data
        ]);
    }

    public function insertAktivitasKuliahMahasiswa(array $data)
    {
        return $this->call('InsertAktivitasKuliahMahasiswa', ['record' => $data]);
    }

    public function getListPerkuliahanMahasiswa(array $filter = [])
    {
        return $this->call('GetListPerkuliahanMahasiswa', [
            'filter' => $filter['filter'] ?? '',
            'order' => $filter['order'] ?? '',
            'limit' => $filter['limit'] ?? 0,
            'offset' => $filter['offset'] ?? 0,
        ]);
    }

    public function getCountAktivitasKuliahMahasiswa(array $filter = [])
    {
        return $this->call('GetCountPerkuliahanMahasiswa', [
            'filter' => $filter['filter'] ?? '',
        ]);
    }

    public function updateRiwayatPendidikanMahasiswa(array $key, array $data)
    {
        return $this->call('UpdateRiwayatPendidikanMahasiswa', [
            'key' => $key,
            'record' => $data
        ]);
    }

    public function getDictionary(array $filter = [])
    {
        return $this->call('GetDictionary', [
            'fungsi' => $filter['fungsi'] ?? '',
        ]);
    }

}
