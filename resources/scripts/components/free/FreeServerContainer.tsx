import React, { useEffect, useState } from 'react';
import PageContentBlock from '@/components/elements/PageContentBlock';
import Button from '@/components/elements/Button';
import useFlash from '@/plugins/useFlash';
import http from '@/api/http';
import useSWR from 'swr';
import { httpErrorToHuman } from '@/api/http';

interface FreeServer {
    id: number;
    server_id: number;
    expires_at: string;
    remaining: number;
    can_extend: boolean;
}

const fetcher = () =>
    http.get('/api/client/free-server').then((r) => r.data.data?.attributes ?? null);

export default function FreeServerContainer() {
    const { addError, clearFlashes } = useFlash();
    const { data, mutate } = useSWR<FreeServer | null>('/api/client/free-server', fetcher, {
        refreshInterval: 60000,
    });
    const [remaining, setRemaining] = useState<number>(0);

    useEffect(() => {
        setRemaining(data?.remaining ?? 0);
        if (!data) return;
        const timer = setInterval(() => {
            setRemaining((s) => (s > 0 ? s - 1 : 0));
        }, 1000);
        return () => clearInterval(timer);
    }, [data?.id]);

    const claim = () => {
        clearFlashes('free');
        http.post('/api/client/free-server')
            .then((r) => mutate(r.data.data.attributes, false))
            .catch((e) => addError({ key: 'free', message: httpErrorToHuman(e) }));
    };

    const extend = () => {
        clearFlashes('free');
        http.post('/api/client/free-server/extend')
            .then((r) => mutate(r.data.data.attributes, false))
            .catch((e) => addError({ key: 'free', message: httpErrorToHuman(e) }));
    };

    const formatTime = (secs: number) => {
        const hours = Math.floor(secs / 3600);
        const minutes = Math.floor((secs % 3600) / 60);
        const seconds = secs % 60;
        return `${hours}h ${minutes}m ${seconds}s`;
    };

    return (
        <PageContentBlock title={'Free Server'} showFlashKey={'free'}>
            {!data ? (
                <div className='text-center'>
                    <p className='mb-4'>Claim your complimentary server instance.</p>
                    <Button onClick={claim}>Claim Server</Button>
                </div>
            ) : (
                <div className='text-center'>
                    <p className='mb-2'>Server ID: {data.server_id}</p>
                    <p className='mb-4'>Time remaining: {formatTime(remaining)}</p>
                    <Button onClick={extend} disabled={!data.can_extend}>
                        Extend
                    </Button>
                </div>
            )}
        </PageContentBlock>
    );
}
